<?php
require_once(__DIR__ . "/../vendor/autoload.php");

use Mike42\ImagePhp\Image;

// Write colorwheel.ppm out as each supported format
$img = Image::fromFile(dirname(__FILE__). "/resources/colorwheel.ppm");
$img2 = $img -> toRgb();
$img2 -> write("colorwheel-original.pbm");
$img3 = $img -> toGrayscale();
$img3 -> write("colorwheel-gray.pgm");
$img4 = $img -> toBlackAndWhite();
$img4 -> write("colorwheel-black.pbm");

// Write gradient.pgm out as each supported format
$img = Image::fromFile(dirname(__FILE__). "/resources/gradient.pgm");
$img2 = $img -> toGrayscale();
$img2 -> write("gradient-original.pgm");
$img3 = $img -> toRgb();
$img3 -> write("gradient-color.ppm");
$img4 = $img -> toBlackAndWhite();
$img4 -> write("gradient-black.pbm");

// Write 5x7hex.pbm out as each supported format
$img = Image::fromFile(dirname(__FILE__). "/resources/5x7hex.pbm");
$img2 = $img -> toBlackAndWhite();
$img2 -> write("font-original.pbm");
$img3 = $img -> toRgb();
$img3 -> write("font-color.ppm");
$img4 = $img -> toBlackAndWhite();
$img4 -> write("font-grayscale.pgm");

