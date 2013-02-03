<?php
require 'Thumbnails.php';
header("Pragma: public");
header('Content-disposition: filename=image_thumb.png');
header("Content-type: image/png");
header('Content-Transfer-Encoding: binary');
ob_clean();
flush();
$n = Thumbnails::createThumb('img.jpg', null, 1024,350, Thumbnails::IMAGE_CENTER | Thumbnails::IMAGE_TOUCH_OUTSIDE);
$n->printThumbnail(Thumbnails::IMAGE_FORMAT_PNG);
?>