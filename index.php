<?php
require 'Thumbnails.php';

Thumbnails::createThumb('img.png', 'th.gif', 100, 100, Thumbnails::IMAGE_CENTER | Thumbnails::IMAGE_POS_BOTTOM | Thumbnails::IMAGE_POS_RIGHT, Thumbnails::OUTPUT_GIF);

?>
