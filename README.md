This little script extracts the current water level of the lake constance from the external website http://www.pegelonline.wsv.de/gast/stammdaten?pegelnr=0906 and displays the information in a more convenient way.

The script generates a SVG picture which displays the current water level like a fuel gauge of a car.

The script has been tested with Firefox 3.6, 4, Chromium 11 and Opera 11.1.

In case you don't want to use a webserver, you can generate the SVG by executing the following command.

php levelmeter.php > levelmeter.svg

You can use the SVG Rasterizer of the batik toolkit http://xmlgraphics.apache.org/batik/tools/rasterizer.html in order to generate a PNG.

java -jar batik-rasterizer.jar levelmeter.svg
