<?php
include 'Sitemap.php';

$mySitemap = new Sitemap('http://localhost/sitemap-data/', 'demo', '123');

echo $mySitemap->getSitemapString();

$mySitemap->saveSitemapFile('Sitemap.xml');