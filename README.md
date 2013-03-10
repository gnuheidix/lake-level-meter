This script fetches the current water level of the Lake Constance from the Pegelonline webservice http://www.pegelonline.wsv.de/webservice/ueberblick and displays it in a more convenient way.

By executing it, you will get a SVG picture which looks like a fuel gauge of a car.

$ php levelmeter.php > levelmeter.svg

The SVG has been successfully tested with Firefox 3.6, 4, Chromium 11 and Opera 11.1.

You can use the SVG rasterizer of the batik toolkit http://xmlgraphics.apache.org/batik/tools/rasterizer.html in order to generate a PNG.

$ java -jar batik-rasterizer.jar levelmeter.svg
