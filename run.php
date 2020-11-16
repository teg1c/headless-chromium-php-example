<?php

use Yurun\Util\YurunHttp\Http\Psr7\ServerRequest;

require_once './vendor/autoload.php';
require_once './ChromeDownloader.php';

$chromeDownloader = new ChromeDownloader();
$client = new \GuzzleHttp\Client();
$request = new ServerRequest('https://tegic.me');
$response = $chromeDownloader->download($request);

//var_dump($response->getBody()->getContents());