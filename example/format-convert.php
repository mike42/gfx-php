<?php
require_once(__DIR__ . "/../vendor/autoload.php");

use Mike42\ImagePhp\Image;

// Write colorwheel.ppm out as each supported format
$img = Image::fromFile(dirname(__FILE__). "/resources/colorwheel.ppm");
$img -> write("colorwheel.bmp");
$img -> write("colorwheel.pbm");
$img -> write("colorwheel.pgm");
$img -> write("colorwheel.png");
$img -> write("colorwheel.ppm");

// Write gradient.pgm out as each supported format
$img = Image::fromFile(dirname(__FILE__). "/resources/gradient.pgm");
$img -> write("gradient.bmp");
$img -> write("gradient.pbm");
$img -> write("gradient.pgm");
$img -> write("gradient.png");
$img -> write("gradient.ppm");

// Write 5x7hex.pbm out as each supported format
$img = Image::fromFile(dirname(__FILE__). "/resources/5x7hex.pbm");
$img -> write("font.bmp");
$img -> write("font.pbm");
$img -> write("font.pgm");
$img -> write("font.png");
$img -> write("font.ppm");
