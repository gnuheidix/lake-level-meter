<?php
/*
 * a simple water level meter
 * 
 * 05/2011 Thomas Heidrich
 * based on the work of Dr. O. Hoffmann
 *     see http://hoffmann.bplaced.net/hilfe.php?me=svg2&in=svguhr&stil=_
 *
*/

// dimension initialization
settype ($width, 'integer');
$width = 400;
settype ($height, 'integer');
$height = 400;

// set dimension if configured
if(isset($_GET['width']) && isset($_GET['height'])) {
    $paramW = $_GET['width'];
    $paramH = $_GET['height'];
    if ($paramW > 0 && $paramH > 0) {
        $width = $paramW;
        $height = $paramH;
    }
}

// write header
header("Content-type: image/svg+xml");
echo "<?xml version=\"1.0\" encoding=\"iso-8859-1\" ?> \n";
?>

<!DOCTYPE svg PUBLIC "-//W3C//DTD SVG 1.1//EN" 
  "http://www.w3.org/Graphics/SVG/1.1/DTD/svg11.dtd">
<svg width="<?php echo $width ;?>px" 
height="<?php echo $height ;?>px" 
viewBox="0 0 1000 1000" 
preserveAspectRatio="none" 
xmlns="http://www.w3.org/2000/svg" 
version="1.1"
xml:lang="de">
  <title>lake-level-meter</title>
  <rect fill="black" x="0" y="0" 
  width="1000" height="402"/>
    
<?php
echo "<g transform=\"translate(500,500)\" 
 stroke-width=\"10\"> \n";

// draw small pitch lines
$maxr=480;
$minr=440;

for ($i = 34; $i <= 56; $i++) {
    $alpha=(M_PI/30.0)*$i;

    $x1=round($maxr*cos($alpha));
    $y1=round($maxr*sin($alpha));
    $x2=round($minr*cos($alpha));
    $y2=round($minr*sin($alpha));

    echo "<line stroke=\"rgb(255,255,255)\" x1=\"$x1\" y1=\"$y1\" x2=\"$x2\" y2=\"$y2\" /> \n";
}

// draw big pitch lines
$maxr=490;
$minr=420;

for ($i = 7; $i <= 11; $i++) {
    $alpha=(M_PI/6.0)*$i;

    $x1=round($maxr*cos($alpha));
    $y1=round($maxr*sin($alpha));
    $x2=round($minr*cos($alpha));
    $y2=round($minr*sin($alpha));
    echo "<line stroke=\"rgb(255,255,255)\" x1=\"$x1\" y1=\"$y1\" x2=\"$x2\" y2=\"$y2\" stroke-width=\"16\"/> \n";
}

// draw text
?>
<text x="300" y="-110" font-size="100" 
fill="white" font-family="Helvetica, sans-serif">F</text>
<text x="-350" y="-110" font-size="100" 
fill="red" font-family="Helvetica, sans-serif">E</text>
<text x="-199" y="-110" font-size="100" 
fill="white" font-family="Helvetica, sans-serif">280,30 m</text>

<?php
// draw needle
echo "<g><animateTransform 
attributeName=\"transform\" 
attributeType=\"XML\" 
type=\"rotate\" 
from=\"210\" 
to=\"250\" 
dur=\"1s\" 
repeatCount=\"0\" 
additive=\"replace\" 
fill=\"freeze\" /> \n";
echo "<line stroke-width=\"20\" stroke=\"rgb(255,255,255)\" x1=\"300\" y1=\"0\" 
x2=\"420\" y2=\"0\" /> \n";
echo  "</g> \n\n"
     ."</g> \n"
     ."</svg>"
?>
