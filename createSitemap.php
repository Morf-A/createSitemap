<?php

// Параметры
$host     = 'http://localhost/sitemap-data/';
$login    = 'demo';
$password = '123';

// Получаем json-карту сайта
$jsonSiteMap = file_get_contents(
    $host,
    false,
    stream_context_create([
        'http'=>[
            'method'=>'GET',
            'header'=>'Accept-language: en' ."\r\n" .
                'Authorization: Basic ' . base64_encode("$login:$password") . "\r\n"
        ]
    ])
);


$baseUrl = 'http://site.ru/';
//$uriList = [];

$arraySiteMap = json_decode($jsonSiteMap);

function createUriList($tempSiteMap, $path){
    $uriList = [];
    foreach ($tempSiteMap as $parent => $child) {

        if(is_string($child)){
            $uriList[] = $path . $child . '.html';
        }else{
            $newPath = $path . $parent . '/';
            $uriList[] = $newPath;
            $uriList = array_merge($uriList, createUriList($child, $newPath));
        }
    }
    return $uriList;
}


$uriList = array_merge([$baseUrl], createUriList($arraySiteMap, $baseUrl));

print_r($uriList);

