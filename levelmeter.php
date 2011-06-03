<?php
/*
 * a simple water level meter for the lake constance
 *
 * made by Thomas Heidrich - May 2011
 * updated June 2011
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
$level = floatval(str_replace(",", ".", xpathQuery($xpath, $queryLevel)));
$mnw = floatval(str_replace(",", ".", xpathQuery($xpath, $queryMNW)));
$mw = floatval(str_replace(",", ".", xpathQuery($xpath, $queryMW)));
$date = xpathQuery($xpath, $queryTime);

/* calculate needle position
 * needle: min. 210 -- mid. 270 -- max. 330
 * level: min. 250 (MNW) -- mid. 341 (MW)
 * two point function http://www.tutorvista.com/math/two-point-function
 * example: needlePos = 210 + (270 - 210) / (341 - 250) * (level - 250)
*/
$needleMin = 210;
$needleMid = 270;
$needlePos = $needleMin + ($needleMid - $needleMin) / ($mw - $mnw) * ($level - $mnw);

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
<svg width="<?php echo $width; ?>px" height="<?php echo $width/2.4; ?>px" viewBox="0 0 1000 415" preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg" version="1.1" xml:lang="de">
 <rect fill="black" x="0" y="0" width="1000" height="415"/>
<?php
// draw scale
echo  ' <g transform="translate(500,500)" stroke-width="10">'."\n"
     .'<text text-anchor="end" x="495" y="-470" font-size="25" fill="white" font-family="sans-serif">'.$date.'</text>'."\n";

// draw small pitch lines
$maxr = 480;
$minr = 440;
for ($i = 34; $i <= 56; ++$i) {
    $alpha = (M_PI / 30.0) * $i;

    $x1 = round($maxr * cos($alpha));
    $y1 = round($maxr * sin($alpha));
    $x2 = round($minr * cos($alpha));
    $y2 = round($minr * sin($alpha));

    echo '  <line stroke="rgb(';
    if($i < 37 || $i > 53){
        echo "255,0,0";
    }else{
        echo "130,255,130";
    }
    echo ')" x1="'.$x1.'" y1="'.$y1.'" x2="'.$x2.'" y2="'.$y2.'" />'."\n";
}

// draw big pitch lines
$maxr = 490;
$minr = 420;
for ($i = 7; $i <= 11; ++$i) {
    $alpha = (M_PI / 6.0) * $i;

    $x1 = round($maxr * cos($alpha));
    $y1 = round($maxr * sin($alpha));
    $x2 = round($minr * cos($alpha));
    $y2 = round($minr * sin($alpha));
    echo '  <line stroke="rgb(';
    if($i != 7 && $i != 11){
        echo "255,255,255";
    }else{
        echo "255,0,0";
    }
    echo ')" x1="'.$x1.'" y1="'.$y1.'" x2="'.$x2.'" y2="'.$y2.'" stroke-width="16"/>'."\n";
}

// draw text
?>
  <text x="300" y="-110" font-size="100" fill="red" font-family="sans-serif">F</text>
  <text x="-365" y="-110" font-size="100" fill="red" font-family="sans-serif">E</text>
  <text text-anchor="middle" y="-110" font-size="100" fill="white" font-family="sans-serif"><?php echo str_replace(".", ",", ($level / 100))." m"; ?></text>
<?php
// draw needle (E:210 -- F:330)
echo '  <line stroke-width="20" stroke="rgb(255,255,255)" transform="rotate('.$needlePos.')" x1="280" y1="0" x2="420" y2="0">'."\n";
echo '   <animateTransform attributeName="transform" attributeType="XML" type="rotate" from="210" to="'.$needlePos.'" dur="1s" additive="replace" fill="freeze" />'."\n";
echo '  </line>'."\n";
echo  " </g>\n"
     ."</svg>"
?>
