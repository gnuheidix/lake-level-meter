<?php

/**
  * Queries the xpath of a DOMDocument by using DOMXPath - only for single items
  * @param $xpath a DOMXPath instance
  * @param $query a xpath query string
  * @returns the result of the query as string -- "" in case the query fails
**/
function xpathQuery($xpath, $query){
    $entry = $xpath->query($query);
    if($entry->length == 1){
        return $entry->item(0)->nodeValue;
    }else{
        return "";
    }
}

// main program
$url   = 'http://www.pegelonline.wsv.de/gast/stammdaten?pegelnr=0906';
$queryLevel = '/html/body/div/div[7]/table[2]/tr[3]/td[2]';
$queryTime  = '/html/body/div/div[7]/table[2]/tr[3]/td[3]';

$doc = new DOMDocument();
$doc->loadHtmlFile($url);

$xpath = new DOMXPath($doc);

echo xpathQuery($xpath, $queryLevel)."m -- ".xpathQuery($xpath, $queryTime);

?>
