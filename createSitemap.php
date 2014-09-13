<?php

// Параметры
$host     = 'http://localhost/sitemap-data/';
$login    = 'demo';
$password = '123';

// Получаем json-карту сайта
$jsonMap = file_get_contents(
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

echo $jsonMap;