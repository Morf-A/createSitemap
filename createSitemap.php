<?php

// Параметры
$host     = 'http://localhost/sitemap-data/';
$login    = 'demo';
$password = '123';


// Обработчик ошибок
function myErrorHandler($errno, $errstr, $errfile, $errline)
{
    echo 'An error has occurred:' .  $errstr;
    die;
}
set_error_handler('myErrorHandler');


// Правильное кодирование URL
function my_encode($string) {
    return str_replace(
        ['%21', '%2A', '%27', '%28', '%29', '%3B', '%3A', '%40', '%26', '%3D', '%2B', '%24', '%2C', '%2F', '%3F', '%25', '%23', '%5B', '%5D'],
        ['!', '*', "'", "(", ")", ";", ":", "@", "&", "=", "+", "$", ",", "/", "?", "%", "#", "[", "]"],
        rawurlencode(htmlspecialchars(
            $string,
            ENT_QUOTES | ENT_XML1
        ))
    );
}


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
$arraySiteMap = json_decode($jsonSiteMap, true, 512, JSON_BIGINT_AS_STRING);

// Проверка преобраования в массив
if(is_null($arraySiteMap)){
    echo 'File: ' . __FILE__ . ' Line:' . __LINE__ . ' An error has occurred: Could not be converted JSON to array';
    die;
}

$baseUrl = 'http://site.ru/';
function createUriList($tempSiteMap, $path){
    $uriList = [];
    foreach ($tempSiteMap as $parent => $child) {

        if(is_string($child)){
            $uriList[] = $path .
                my_encode($child) .
                '.html';
        }elseif(is_array($child)){
            $newPath = $path . my_encode($parent) . '/';
            $uriList[] = $newPath;
            $uriList = array_merge($uriList, createUriList($child, $newPath));
        }else{
            echo 'File: ' . __FILE__ . ' Line:' . __LINE__ . ' An error has occurred: Bad JSON node';
            die;
        }
    }
    return $uriList;
}

$uriList = array_merge([$baseUrl], createUriList($arraySiteMap, $baseUrl));


// Запишем список uri в виде XML
$xmlSiteMap = '<?xml version="1.0" encoding="UTF-8"?><urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
foreach ($uriList as $key => $uri) {
    $xmlSiteMap .= '<url>';
    $xmlSiteMap .= '<loc>' . $uri . '</loc>';
    $xmlSiteMap .= '<lastmod>' . '2005-01-01' . '</lastmod>';
    $xmlSiteMap .= '<changefreq>' . 'monthly' . '</changefreq>';
    $xmlSiteMap .= '<priority>' . '0.7' . '</priority>';
    $xmlSiteMap .= '</url>';
}
$xmlSiteMap .= '</urlset>';


// Сохранение в файл
file_put_contents(
    'Sitemap.xml',
    $xmlSiteMap
);
