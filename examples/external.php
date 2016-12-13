<?php

if (empty(@$_REQUEST['url'])) {
    header("HTTP/1.0 404 Not Found");
    echo "URL is not specified.";
    exit;
}

$aresUrl = $_REQUEST['url'];
$cacheFile = __DIR__ . "/cache/" . md5($aresUrl . date("YW")) . ".xml";

header('Content-Type: application/xml; charset=utf-8');

if (!is_file($cacheFile)) {
    $response = file_get_contents($aresUrl);
    file_put_contents($cacheFile, $response);
} else {
    $response = file_get_contents($cacheFile);
}

echo $response;
