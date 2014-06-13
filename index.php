<?php
//error_reporting(0);
error_reporting(E_ALL);
//error_reporting(~E_WARNING);
//error_reporting(~E_NOTICE);
libxml_use_internal_errors(true);
/**
 * Author: Michal Schweichler
 * email: michal.schweichler[at]gmail.com
 * www: http://michalschweichler.co.uk
 *
 * Date: 13/06/14
 * Time: 20:30
 */
require_once 'SeoAnalyser.php';

// test.tld
// www.test.tld
// //www.test.tld
// http://test.tld
// http://www.test.tld
// https://test.tld
// https://www.test.tld

$seo = new SeoAnalyser('elance.com');
//$seo = new SeoAnalyser('http://www.reed.co.uk/');
//$seo = new SeoAnalyser('cnn.com');


echo 'url: ' . '<br>';
var_dump($seo->getUrl());
echo '<br>';

echo 'title: ' . '<br>';
var_dump($seo->getTitle());
echo '<br>';

echo 'description: ' . '<br>';
var_dump($seo->getDescription());
echo '<br>';

echo 'keywords: ' . '<br>';
var_dump($seo->getKeywords());
echo '<br>';

echo 'robots: ' . '<br>';
var_dump($seo->getRobots());
echo '<br>';

echo 'charset: ' . '<br>';
var_dump($seo->getCharset());
echo '<br>';

echo 'external links: ' . '<br>';
var_dump($seo->getExternalLinks());
echo '<br>';

echo 'internal links: ' . '<br>';
var_dump($seo->getInternalLinks());
echo '<br>';

//echo 'all links: ' . '<br>';
//var_dump($seo->getAllLinks());
//echo '<br>';