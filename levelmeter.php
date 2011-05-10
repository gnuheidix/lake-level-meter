<?php
/*
 * a simple water level meter
 * 
 * 05/2011 Thomas Heidrich
 * Basiert auf Dr. O. Hoffmann
 *     siehe http://hoffmann.bplaced.net/hilfe.php?me=svg2&in=svguhr&stil=_
 *
*/

// declarations
settype ($width, 'integer');
$width = 400;
settype ($height, 'integer');
$height = 400;

// set width if configured
if(isset($_GET['width']) && isset($_GET['height'])) {
    $paramW = $_GET['width'];
    if ($paramW > 0) {
        $width = $paramW;
    }
}

# Uhrzeit stellen?
if(isset($_GET['zeit'])) {
 $selbstst=$_GET['zeit'];
 $selbststellen=TRUE;
} else {
 $selbstst="0:0:0";
 $selbststellen=FALSE;
}
$uhrzeit[0]=0;
$uhrzeit[1]=0;
$uhrzeit[2]=0;
$uhrzeit=explode(":", $selbstst);

# svg-header senden:
  $content="Content-type: image/svg+xml";
  header($content);
# xml-Zeile senden, vorsichtshalber mit echo ausgeben, falls 
# auf dem server php-short-tags aktiviert sind.
echo "<?xml version=\"1.0\" encoding=\"iso-8859-1\" ?> \n";
?>
<!DOCTYPE svg PUBLIC "-//W3C//DTD SVG 1.1//EN" 
  "http://www.w3.org/Graphics/SVG/1.1/DTD/svg11.dtd">
<svg width="<?php echo $width ;?>px" 
height="<?php echo $width ;?>px" 
viewBox="0 0 1000 1000" 
preserveAspectRatio="none" 
xmlns="http://www.w3.org/2000/svg" 
version="1.1"
xml:lang="de">
  <title>SVG-Beispiel: Uhr - ohne Startzeiteinstellung</title>
  <rect fill="black" x="0" y="0" 
  width="1000" height="402"/>
    
<?php
echo "<g transform=\"translate(500,500)\" 
stroke=\"rgb(255,255,255)\" stroke-width=\"10\"> \n";

# Sekunden- und Minutenskala:
$maxr=480;
$minr=440;

for ($i = 34; $i <= 56; $i++) {
    $alpha=(M_PI/30.0)*$i;

    $x1=round($maxr*cos($alpha));
    $y1=round($maxr*sin($alpha));
    $x2=round($minr*cos($alpha));
    $y2=round($minr*sin($alpha));

    echo "<line x1=\"$x1\" y1=\"$y1\" x2=\"$x2\" y2=\"$y2\" /> \n";
}

# Grobskala Stunden
$maxr=490;
$minr=420;

for ($i = 7; $i <= 11; $i++) {
    $alpha=(M_PI/6.0)*$i;

    $x1=round($maxr*cos($alpha));
    $y1=round($maxr*sin($alpha));
    $x2=round($minr*cos($alpha));
    $y2=round($minr*sin($alpha));
    echo "<line x1=\"$x1\" y1=\"$y1\" x2=\"$x2\" y2=\"$y2\" stroke-width=\"16\"/> \n";
}

# Stundenziffern per Hand positionieren:
?>
<!--<text x="-230" y="-200" font-size="80" 
fill="white" font-family="Deja-Vu, sans-serif">Bodensee</text> -->

<text x="300" y="-110" font-size="100" 
fill="white" font-family="Helvetica, sans-serif">F</text>
<text x="-350" y="-110" font-size="100" 
fill="white" font-family="Helvetica, sans-serif">E</text>

<?php
# Jetzt werden die Zeiger gemalt, 
# durch die Rotations-Animation drehen sich die Linien mit der
# richtigen Umlaufzeit
# Stundenzeiger
/*
echo "<g><animateTransform 
attributeName=\"transform\" 
attributeType=\"XML\" 
type=\"rotate\" 
from=\"0\" 
to=\"360\" 
dur=\"43200s\" 
repeatCount=\"indefinite\" 
additive=\"replace\" 
fill=\"freeze\" /> \n";
echo "<line x1=\"0\" y1=\"0\" 
x2=\"150\" y2=\"0\" stroke-width=\"40\"/> \n";
echo "</g> \n";

# Minutenzeiger
echo "<g><animateTransform 
attributeName=\"transform\" 
attributeType=\"XML\" 
type=\"rotate\" 
from=\"0\" 
to=\"360\" 
dur=\"3600s\" 
repeatCount=\"indefinite\" 
additive=\"replace\" 
fill=\"freeze\" /> \n";
echo "<line x1=\"150\" y1=\"0\" 
x2=\"300\" y2=\"0\" stroke-width=\"20\"/> \n";
echo "</g> \n";
*/
# Sekundenzeiger
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
echo "<line fill=\"#ff0000\" x1=\"300\" y1=\"0\" 
x2=\"420\" y2=\"0\" /> \n";
echo "</g> \n";

echo "</g> \n";
?> 
</svg>
