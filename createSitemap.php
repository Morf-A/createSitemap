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

// Преобразуем json в массив uri
$baseUrl = 'http://site.ru/';

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

// Запишем список uri в виде XML

$xmlSiteMap = new SimpleXMLElement(
        '<?xml version="1.0" encoding="UTF-8"?><urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"></urlset>'
);

foreach ($uriList as $key => $uri) {
    $url = $xmlSiteMap->addChild('url');

    $url->addChild('loc', $uri);
    $url->addChild('lastmod', '2005-01-01');
    $url->addChild('changefreq', 'monthly');
    $url->addChild('priority', '0.7');
}

// Сохранение в файл
file_put_contents(
    'Sitemap.xml',
    $xmlSiteMap->asXml()
);
