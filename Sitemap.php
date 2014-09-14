<?php

class Sitemap {

    private $host;
    private $login;
    private $password;

    private $sitemapString = null;

    public function __construct($host, $login, $password){

        $this->host     = $host;
        $this->login    = $login;
        $this->password = $password;

        set_error_handler(function ($errno, $errstr, $errfile, $errline){
            throw new Exception('An error has occurred: ' .  $errstr);
        });
    }

    // Правильное кодирование URL
    private function my_encode($string) {
        return str_replace(
            ['%21', '%2A', '%27', '%28', '%29', '%3B', '%3A', '%40', '%26', '%3D', '%2B', '%24', '%2C', '%2F', '%3F', '%25', '%23', '%5B', '%5D'],
            ['!', '*', "'", "(", ")", ";", ":", "@", "&", "=", "+", "$", ",", "/", "?", "%", "#", "[", "]"],
            rawurlencode(htmlspecialchars(
                $string,
                ENT_QUOTES | ENT_XML1
            ))
        );
    }

    // Преобразует JSON в ассоциативный массив

    private function createUriList($tempSiteMap, $path){
        $uriList = [];
        foreach ($tempSiteMap as $parent => $child) {

            if(is_string($child)){
                $uriList[] = $path .
                    $this->my_encode($child) .
                    '.html';
            }elseif(is_array($child)){
                $newPath = $path . $this->my_encode($parent) . '/';
                $uriList[] = $newPath;
                $uriList = array_merge($uriList, $this->createUriList($child, $newPath));
            }else{
                throw new Exception('Bad JSON node');
            }
        }
        return $uriList;
    }

    public function getSitemapString() {
        if(is_null($this->sitemapString)){

            // Получаем json-карту сайта
            $jsonSiteMap = file_get_contents(
                $this->host,
                false,
                stream_context_create([
                    'http'=>[
                        'method'=>'GET',
                        'header'=>'Accept-language: en' ."\r\n" .
                            'Authorization: Basic ' . base64_encode("$this->login:$this->password") . "\r\n"
                    ]
                ])
            );


            // Преобразуем json в массив uri
            $arraySiteMap = json_decode($jsonSiteMap, true, 512, JSON_BIGINT_AS_STRING);

            // Проверка преобраования в массив
            if(is_null($arraySiteMap)){
                throw new Exception('Could not be converted JSON to array');
            }

            $baseUrl = 'http://site.ru/';

            $uriList = array_merge([$baseUrl], $this->createUriList($arraySiteMap, $baseUrl));


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
            $this->sitemapString = $xmlSiteMap;
        }
        return $this->sitemapString;
    }

    public function saveSitemapFile($file){
        // Сохранение в файл
        file_put_contents(
            $file,
            $this->getSitemapString()
        );
    }
}

