<?php
/*require 'Thumbnails.php';
header("Pragma: public");
header('Content-disposition: filename=image_thumb.png');
header("Content-type: image/png");
header('Content-Transfer-Encoding: binary');
ob_clean();
flush();
Thumbnails::createThumb('img.jpg', null, 400,410, Thumbnails::IMAGE_CENTER | Thumbnails::IMAGE_TOUCH_OUTSIDE | Thumbnails::IMAGE_POS_RIGHT, Thumbnails::IMAGE_FORMAT_PNG, array('r'=>255,'g'=>255,'b'=>255));
*/
$m = base_convert('f', 16, 10);
var_dump($m);
?>