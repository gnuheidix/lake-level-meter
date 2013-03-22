<?php
/*
 * a simple water level meter for the lake constance
 *
 * made by Thomas Heidrich - May 2011
 * updated July 2011
 * updated March 2013
 *
 * SVG based on the work of Dr. O. Hoffmann
 *     see http://hoffmann.bplaced.net/hilfe.php?me=svg2&in=svguhr&stil=_
 *
 * Usage: Simply run the script and hope that pegelonline.wsv.de is online. ;-)
 *
 * License: CC-BY-SA 3.0 -- http://creativecommons.org/licenses/by-sa/3.0/de/
 *
*/

define('RED', '255,0,0');
define('YELLOW', '255,255,0');
define('GREEN', '130,255,130');
define('BLACK', '0,0,0');
define('WHITE', '255,255,255');
define('SVG_WIDTH', '400px');
define('MNW', 262);
define('MW', 341);
define('WATER_ERROR_LEVEL', -1);
define('DATE_FORMAT', '%d.%m.%Y %R %Z');

$url   = 'http://www.pegelonline.wsv.de/webservices/rest-api/v2/stations/KONSTANZ/W.json?includeCurrentMeasurement=true';
$timeout = 4;
$waterData = fetchWaterLevelFromWebservice($url, $timeout);

header("Content-type: image/svg+xml");
echo renderSVG($waterData['level'], $waterData['timestamp']);


// ######################### FUNCTIONS #############################
/**
 * queries the webservice and extracts the current waterlevel und the timestamp of the measurement
 * @param string $url where to fetch the JSON from
 * @param int $timeout give up after...?
 * @return array associative array with the keys 'level' and 'timestamp'
 */
function fetchWaterLevelFromWebservice($url, $timeout){
    $retval = array(
        'level' => WATER_ERROR_LEVEL,
        'timestamp' => '',
    );
    $streamContext = stream_context_create(
        array(
            'http' => array(
                'timeout' => $timeout
            )
        )
    );
    $content = @file_get_contents($url, 0, $streamContext);
    if($content){
        $restData = (array)json_decode($content, true);
        
        $retval['level'] = extractWaterLevel($restData);
        $retval['timestamp'] = extractTimestamp($restData);
    }
    return $retval;
}

/**
 * parses the webservice response and extracts the waterlevel of the current measurement
 * @param array $restData
 * @return float water level or WATER_ERROR_LEVEL
 */
function extractWaterLevel($restData){
    $retval = WATER_ERROR_LEVEL;
    if(!empty($restData['currentMeasurement']['value'])){
        $waterLevel = (float)($restData['currentMeasurement']['value']);
        if($waterLevel > 0 && $waterLevel < 768){
            $retval = $waterLevel;
        }
    }
    return $retval;
}

/**
 * parses the webservice response and extracts the timestamp of the current measurement
 * @param array $restData
 * @return string timestamp or an empty string
 */
function extractTimestamp($restData){
    $retval = '';
    if(!empty($restData['currentMeasurement']['timestamp'])){
        $timestamp = strtotime($restData['currentMeasurement']['timestamp']);
        if($timestamp){
            $retval = strftime(DATE_FORMAT, $timestamp);
        }
    }
    return $retval;
}

/**
 * calculates needle position
 * needle: min. 210 -- mid. 270 -- max. 330
 * level: min. 250 (MNW) -- mid. 341 (MW)
 * two point function http://www.tutorvista.com/math/two-point-function
 * example: needlePos = 210 + (270 - 210) / (341 - 250) * (level - 250)
 *
 * @param float $waterLevel water level in centimeters 
 * @return float
 */
function getNeedlePosInDeg($waterLevel){
    $needleMin = 180;
    $needleMid = 240;
    
    $retval = 0;
    if($waterLevel !== WATER_ERROR_LEVEL){
        $retval = $needleMin + ($needleMid - $needleMin) / (MW - MNW) * ($waterLevel - MNW);
    }
    return $retval;
}

/**
 * @param float $degree
 * @param int $minRadius
 * @param int $maxRadius
 * @param string $color rgb values in the range 0 - 255 separated by commas e.g. 23,45,67
 * @param int $strokeWidth
 * @return string SVG line element
 */
function renderPitchLine($degree, $minRadius, $maxRadius, $color, $strokeWidth){
    $x1 = round($maxRadius * cos($degree));
    $y1 = round($maxRadius * sin($degree));
    $x2 = round($minRadius * cos($degree));
    $y2 = round($minRadius * sin($degree));
    return <<<END
  <line stroke="rgb($color)" x1="$x1" y1="$y1" x2="$x2" y2="$y2" stroke-width="$strokeWidth"/>

END;
}

/**
 * @param int $angle
 * @return float
 */
function degToRad($angle){
    return M_PI * $angle / 180;
}

/**
 * @return string
 */
function renderSmallPitchLines(){
    $retval = '';
    for ($i = 25; $i <= 75; ++$i) {
        $color = BLACK;
        if($i < 28){
            $color = RED;
        }elseif($i < 34){
            $color = YELLOW;
        }elseif($i < 57){
            $color = GREEN;
        }elseif($i < 63){
            $color = YELLOW;
        }else{
            $color = RED;
        }
        $retval .= renderPitchLine((M_PI / 30.0) * $i, 440, 480, $color, 10);
    }
    return $retval;
}

/**
 * @return string
 */
function renderBigPitchLines(){
    $retval = '';
    for ($i = 5; $i <= 15; ++$i) {
        $color = BLACK;
        if($i < 6){
            $color = RED;
        }elseif($i < 7){
            $color = YELLOW;
        }elseif($i < 12){
            $color = GREEN;
        }elseif($i < 13){
            $color = YELLOW;
        }else{
            $color = RED;
        }
        $retval .= renderPitchLine((M_PI / 6.0) * $i, 420, 490, $color, 16);
    }
    return $retval;
}

/**
 * @param float $waterLevel in centimeters
 * @return string waterlevel in meters with comma as decimal divider or "Fehler"
 */
function getWaterLevelInMeters($waterLevel){
    if($waterLevel !== WATER_ERROR_LEVEL){
        $retval = str_replace(".", ",", ($waterLevel / 100)) . ' m';
    }else{
        $retval = 'Fehler';
    }
    return $retval;
}

/**
 * 
 * Enter description here ...
 * @param float $waterLevel in centimeters
 * @param unknown_type $color
 */
function renderNeedle($waterLevel, $color){
    $retval = '';
    $waterLevelNeedlePositionInDeg = getNeedlePosInDeg($waterLevel);
    
    if($waterLevelNeedlePositionInDeg > 0){
        $retval = <<<END
  <line stroke-width="20" stroke="rgb($color)" transform="rotate($waterLevelNeedlePositionInDeg)" x1="280" y1="0" x2="420" y2="0">
   <animateTransform attributeName="transform" attributeType="XML" type="rotate" from="140" to="$waterLevelNeedlePositionInDeg" dur="2s" additive="replace" fill="freeze" />
  </line>
END;
    }
    return $retval;
}

/**
 * renders the SVG itself
 * @param float $waterLevel the waterlevel in centimeters
 * @param string $dateText the string to be displayed in the upper right corner
 * @return string the complete SVG
 */
function renderSVG($waterLevel, $dateText){
    // load all dynamic SVG parts
    $greenColor = GREEN;
    $yellowColor = YELLOW;
    $whiteColor = WHITE;
    $blackColor = BLACK;
    $svgWidth = SVG_WIDTH;
    
    $smallPitchLines = renderSmallPitchLines();
    $bigPitchLines = renderBigPitchLines();
    $needle = renderNeedle($waterLevel, $whiteColor);
    
    $waterLevelInMeters = getWaterLevelInMeters($waterLevel, $whiteColor);
    
    // render min. level from 1858-02-17 - 2.26 meters
    $historicalMinLevel = renderPitchLine(degToRad(getNeedlePosInDeg(226)), 240, 490, WHITE, 5);
    // render max. level from 1999-06-11 - 5.65 meters
    $historicalMaxLevel = renderPitchLine(degToRad(getNeedlePosInDeg(565)), 240, 490, WHITE, 5);
    // render HMO - 4.80 meters - Wasserstand der Hochwasser-Melde-Ordnung
    $hmoLevel = renderPitchLine(degToRad(getNeedlePosInDeg(480)), 240, 490, YELLOW, 5);
    // render MNW - Mittelwert niedrigster Wasserstände
    $mnwLevel = renderPitchLine(degToRad(getNeedlePosInDeg(MNW)), 240, 490, YELLOW, 5);
    // render MW - Mittelwert Wasserstände
    $mwLevel = renderPitchLine(degToRad(getNeedlePosInDeg(MW)), 240, 490, GREEN, 5);
    
    $retval = <<<END
<?xml version="1.0" encoding="iso-8859-1" ?>
<!DOCTYPE svg PUBLIC "-//W3C//DTD SVG 1.1//EN" "http://www.w3.org/Graphics/SVG/1.1/DTD/svg11.dtd">
<svg width="$svgWidth" height="$svgWidth" viewBox="0 0 1000 1000" preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg" version="1.1" xml:lang="de">
 <rect fill="rgb($blackColor)" x="0" y="0" width="1000" height="1000"/>
 <g transform="translate(500,500)" stroke-width="10">
$smallPitchLines$bigPitchLines$historicalMinLevel$historicalMaxLevel$hmoLevel$mnwLevel$mwLevel$needle
  <text text-anchor="end" x="495" y="-470" font-size="25" fill="rgb($whiteColor)" font-family="sans-serif">$dateText</text>
  <text x="-130" y="-173" font-size="30" fill="rgb($greenColor)" font-family="sans-serif">MW</text>
  <text x="149" y="-43" font-size="30" fill="rgb($yellowColor)" font-family="sans-serif">HMO</text>
  <text x="65" y="195" font-size="30" fill="rgb($whiteColor)" font-family="sans-serif">1999</text>
  <text x="-210" y="120" font-size="30" fill="rgb($whiteColor)" font-family="sans-serif">1858</text>
  <text x="-233" y="13" font-size="30" fill="rgb($yellowColor)" font-family="sans-serif">MNW</text>
  <text x="-420" y="380" font-size="100" fill="rgb($whiteColor)" font-family="sans-serif">$waterLevelInMeters</text>
 </g>
</svg>
END;
    
    return $retval;
}
