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

/**
  * queries the xpath of a DOMDocument by using DOMXPath - only for single items
  * @param $xpath a DOMXPath instance
  * @param $query a xpath query string
  * @returns the result of the query as string -- "" in case the query fails
**/
function xpathQuery($xpath, $query){
    $entry = $xpath->query($query);
    return $entry->length == 1 ? $entry->item(0)->nodeValue : "";
}

// constants for the queries
$url   = 'http://www.pegelonline.wsv.de/gast/stammdaten?pegelnr=0906';
$queryLevel = '/html/body/div/div[7]/table[2]/tr[3]/td[2]';
$queryTime  = '/html/body/div/div[7]/table[2]/tr[3]/td[3]';
$queryMNW = '/html/body/div/div[7]/table[3]/tr[2]/td[2]';
$queryMW = '/html/body/div/div[7]/table[3]/tr[3]/td[2]';

// get the website
$doc = new DOMDocument();
$doc->loadHtmlFile($url);

// extract the data
$xpath = new DOMXPath($doc);
$docLevel = floatval(str_replace(",", ".", xpathQuery($xpath, $queryLevel)));
$docMnw = floatval(str_replace(",", ".", xpathQuery($xpath, $queryMNW)));
$docMw = floatval(str_replace(",", ".", xpathQuery($xpath, $queryMW)));
$date = xpathQuery($xpath, $queryTime);

/*
// debug test values
$docLevel = 480.10;
$docMnw = 262.00;
$docMw = 341.00;
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
for ($i = 25; $i <= 76; ++$i) {
    $color = "0,0,0";
    if($i < 28){
        $color = "255,0,0";
    }elseif($i < 33){
        $color = "255,255,0";
    }elseif($i < 56){
        $color = "130,255,130";
    }elseif($i < 65){
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

// draw min. level from 1996-03-20 - 2.33 meters
drawPitchLine(degToRad(getNeedlePos(233, $docMw, $docMnw)), 240, 490, "255,255,255", 5);

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
  <text x="100" y="170" font-size="30" fill="white" font-family="sans-serif">1999</text>
  <text x="-260" y="80" font-size="30" fill="white" font-family="sans-serif">1996</text>
  <text x="-233" y="13" font-size="30" fill="yellow" font-family="sans-serif">MNW</text>
  <text x="-400" y="380" font-size="100" fill="white" font-family="sans-serif"><?php echo str_replace(".", ",", ($docLevel / 100))." m"; ?></text>
<?php
// draw needle (E:210 -- F:330)
echo '  <line stroke-width="20" stroke="rgb(255,255,255)" transform="rotate('.getNeedlePos($docLevel, $docMw, $docMnw).')" x1="280" y1="0" x2="420" y2="0">'."\n";
echo '   <animateTransform attributeName="transform" attributeType="XML" type="rotate" from="140" to="'.getNeedlePos($docLevel, $docMw, $docMnw).'" dur="2s" additive="replace" fill="freeze" />'."\n";
echo '  </line>'."\n";
echo  " </g>\n"
     ."</svg>"
?>
