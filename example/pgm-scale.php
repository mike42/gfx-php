<?php
require_once(__DIR__ . "/../vendor/autoload.php");

use Mike42\ImagePhp\Image;

// Scale a gray image
$img = Image::fromFile(dirname(__FILE__). "/resources/gradient.pgm");
$img -> scale(20, 20);
$img -> write("gradient-small.pgm");

$img = Image::fromFile(dirname(__FILE__). "/resources/gradient.pgm");
$img -> scale(60, 60);
$img -> write("gradient-large.pgm");

