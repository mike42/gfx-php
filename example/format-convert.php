<?php
require_once(__DIR__ . "/../vendor/autoload.php");

use Mike42\ImagePhp\Image;

// Write original back
$img = Image::fromFile(dirname(__FILE__). "/resources/colorwheel.ppm");
$img -> write("colorwheel-original.pbm");

// Write 
$img = Image::fromFile(dirname(__FILE__). "/resources/colorwheel.ppm");
$img2 = $img -> toGrayscale();
$img2 -> write("colorwheel-gray.pgm");

$img = Image::fromFile(dirname(__FILE__). "/resources/colorwheel.ppm");
$img3 = $img -> toBlackAndWhite();
$img3 -> write("font-large.pbm");

