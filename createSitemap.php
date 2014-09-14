<?php
include 'Sitemap.php';

$mySitemap = new Sitemap('http://localhost/sitemap-data/', 'demo', '123');

try {
    echo $mySitemap->getSitemapString();
    $mySitemap->saveSitemapFile('Sitemap.xml');
} catch(Exception $e) {
    echo 'Exception: ',  $e->getMessage(), "\n";
}
