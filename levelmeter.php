<?php
/*
 * a simple water level meter for the lake constance
 *
 * made by Thomas Heidrich - May 2011
 * updated July 2011
 *
 * SVG based on the work of Dr. O. Hoffmann
 *     see http://hoffmann.bplaced.net/hilfe.php?me=svg2&in=svguhr&stil=_
 *
 * Usage: Simply run the script and hope that pegelonline.wsv.de is online. ;-)
 * Param: The parameter width can be set in order to manipulate the size of the
 *        water level meter
 * Example: http://localhost/levelmeter.php?width=200
 *
 * License: CC-BY-SA 3.0 -- http://creativecommons.org/licenses/by-sa/3.0/de/
 *
*/

// deactivate warnings in case the source page has an invalid source code
ini_set('display_errors', 0);
setlocale(LC_TIME, "de_DE.utf8");

// constants for the queries
$url   = 'http://www.pegelonline.wsv.de/webservices/rest-api/v2/stations/KONSTANZ/W.json?includeCurrentMeasurement=true';

// init vars
$ctx = stream_context_create(
    array(
        'http' => array(
            'timeout' => 4
        )
    )
);
$date = 'Fehler beim Datenabruf!';
$docLevel = 0;

// get the website
$content = file_get_contents($url, 0, $ctx);
if($content){
    $restData = json_decode($content, true);
    if(!empty($restData['currentMeasurement']['value']) && !empty($restData['currentMeasurement']['timestamp'])){
        $docLevel = $restData['currentMeasurement']['value'];
        $date = strftime('%d.%m.%Y %R %Z', strtotime($restData['currentMeasurement']['timestamp']));
    }
}

/*
// debug test values
$docLevel = 391.5;
*/
$docMnw = 262;
$docMw = 341;
/*
$date = "Debugmode";
*/
/* calculate needle position
 * needle: min. 210 -- mid. 270 -- max. 330
 * level: min. 250 (MNW) -- mid. 341 (MW)
 * two point function http://www.tutorvista.com/math/two-point-function
 * example: needlePos = 210 + (270 - 210) / (341 - 250) * (level - 250)
*/
function getNeedlePos($level, $mw, $mnw){
    $needleMin = 180;
    $needleMid = 240;
    return $needleMin + ($needleMid - $needleMin) / ($mw - $mnw) * ($level - $mnw);
}

/* draws a pitch line */
function drawPitchLine($alpha, $minr, $maxr, $color, $strokeWidth){
    $x1 = round($maxr * cos($alpha));
    $y1 = round($maxr * sin($alpha));
    $x2 = round($minr * cos($alpha));
    $y2 = round($minr * sin($alpha));
    echo '  <line stroke="rgb('.$color.')" x1="'.$x1.'" y1="'.$y1.'" x2="'.$x2.'" y2="'.$y2.'" stroke-width="'.$strokeWidth.'"/>'."\n";
}

/* converts an angle from deg to rad */
function degToRad($angle){
    return M_PI * $angle / 180;
}

// dimension initialization
settype ($width, 'integer');
$width = 400;

// set dimension if configured
if(isset($_GET['width'])) {
    $paramW = $_GET['width'];
    if ($paramW > 0) {
        $width = $paramW;
    }
}

// write header
header("Content-type: image/svg+xml");
echo "<?xml version=\"1.0\" encoding=\"iso-8859-1\" ?> \n";
?>
<!DOCTYPE svg PUBLIC "-//W3C//DTD SVG 1.1//EN" "http://www.w3.org/Graphics/SVG/1.1/DTD/svg11.dtd">
<svg width="<?php echo $width; ?>px" height="<?php echo $width; ?>px" viewBox="0 0 1000 1000" preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg" version="1.1" xml:lang="de">
 <rect fill="black" x="0" y="0" width="1000" height="1000"/>
<?php
// draw scale
echo  ' <g transform="translate(500,500)" stroke-width="10">'."\n"
     .'  <text text-anchor="end" x="495" y="-470" font-size="25" fill="white" font-family="sans-serif">'.$date.'</text>'."\n";

// draw small pitch lines
for ($i = 25; $i <= 75; ++$i) {
    $color = "0,0,0";
    if($i < 28){
        $color = "255,0,0";
    }elseif($i < 34){
        $color = "255,255,0";
    }elseif($i < 57){
        $color = "130,255,130";
    }elseif($i < 63){
        $color = "255,255,0";
    }else{
        $color = "255,0,0";
    }
    drawPitchLine((M_PI / 30.0) * $i, 440, 480, $color, 10);
}

// draw big pitch lines
for ($i = 5; $i <= 15; ++$i) {
    $color = "0, 0, 0";
    
    if($i < 6){
        $color = "255,0,0";
    }elseif($i < 7){
        $color = "255,255,0";
    }elseif($i < 12){
        $color = "130,255,130";
    }elseif($i < 13){
        $color = "255,255,0";
    }else{
        $color = "255,0,0";
    }
    drawPitchLine((M_PI / 6.0) * $i, 420, 490, $color, 16);
}

// draw min. level from 1858-02-17 - 2.26 meters
drawPitchLine(degToRad(getNeedlePos(226, $docMw, $docMnw)), 240, 490, "255,255,255", 5);

// draw min. level from 1999-06-11 - 5.65 meters
drawPitchLine(degToRad(getNeedlePos(565, $docMw, $docMnw)), 240, 490, "255,255,255", 5);

// draw HMO - 4.80 meters
drawPitchLine(degToRad(getNeedlePos(480, $docMw, $docMnw)), 240, 490, "255,255,0", 5);

// draw MNW
drawPitchLine(degToRad(getNeedlePos($docMnw, $docMw, $docMnw)), 240, 490, "255,255,0", 5);

// draw MW
drawPitchLine(degToRad(getNeedlePos($docMw, $docMw, $docMnw)), 240, 490, "130,255,130", 5);

// draw text
?>
  <text x="-130" y="-173" font-size="30" fill="rgb(130,255,130)" font-family="sans-serif">MW</text>
  <text x="149" y="-43" font-size="30" fill="yellow" font-family="sans-serif">HMO</text>
  <text x="65" y="195" font-size="30" fill="white" font-family="sans-serif">1999</text>
  <text x="-210" y="120" font-size="30" fill="white" font-family="sans-serif">1858</text>
  <text x="-233" y="13" font-size="30" fill="yellow" font-family="sans-serif">MNW</text>
  <text x="-420" y="380" font-size="100" fill="white" font-family="sans-serif"><?php echo str_replace(".", ",", ($docLevel / 100))." m"; ?></text>
<?php
// draw needle
echo '  <line stroke-width="20" stroke="rgb(255,255,255)" transform="rotate('.getNeedlePos($docLevel, $docMw, $docMnw).')" x1="280" y1="0" x2="420" y2="0">'."\n";
echo '   <animateTransform attributeName="transform" attributeType="XML" type="rotate" from="140" to="'.getNeedlePos($docLevel, $docMw, $docMnw).'" dur="2s" additive="replace" fill="freeze" />'."\n";
echo '  </line>'."\n";
echo  " </g>\n"
     ."</svg>"
?>
