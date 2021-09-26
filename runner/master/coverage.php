<?php

if (count($argv) !== 4) {
    echo "Invocation php coverage.php path/to/clover.xml path/to/crap4j.xml path/to/output.json\n";
    exit(1);
}

$cloverfile = $argv[1];
$crap4jfile = $argv[2];
$outputfile = $argv[3];

$stats = (object) [];

function get_crap4j_stats(string $filename): object {
    $doc = new DOMDocument();
    $doc->loadXML(file_get_contents($filename));
    $xpath = new DOMXpath($doc);
    $results = $xpath->query('//crap_result/stats');

    $data = (object) [];
    if ($results) {
        $item = $results->item(0);
        foreach ($item->childNodes as $property) {
            if ($property->nodeType === XML_ELEMENT_NODE) {
                $data->{$property->nodeName} = $property->nodeValue;
            }
        }
    }

    return $data;
}

function get_clover_stats(string $filename): object {
    $doc = new DOMDocument();
    $doc->loadXML(file_get_contents($filename));
    $xpath = new DOMXpath($doc);
    $results = $xpath->query('//coverage/project/metrics');

    $data = (object) [];
    if ($results) {
        $item = $results->item(0);
        foreach ($item->attributes as $property) {
            $data->{$property->nodeName} = $property->nodeValue;
        }
    }

    return $data;
}

$stats->clover = get_clover_stats($cloverfile);
$stats->crap4j = get_crap4j_stats($crap4jfile);

file_put_contents($outputfile, json_encode($stats, JSON_PRETTY_PRINT));
