<?php
$t1 = microtime(true);
require 'Thumbnails.php';
$n = new Thumbnails('cancel48.png');
$n->doThumbnail(1024,768,  Thumbnails::IMAGE_CENTER | Thumbnails::IMAGE_TOUCH_OUTSIDE)
    ->saveAs('img3.gif', Thumbnails::IMAGE_FORMAT_GIF);
$t1 = microtime(true) - $t1;
$t2 = microtime(true);
$n1 = imagecreatefromjpeg('img.jpg');
$t2 = microtime(true) - $t2;
//$s = $n->getAsString(Thumbnails::IMAGE_FORMAT_PNG);
echo $t1. ' ' . $t2;
?>