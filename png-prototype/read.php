<?php

require_once("../vendor/autoload.php");

use Mike42\GfxPhp\Codec\Common\DataBlobInputStream;
use Mike42\GfxPhp\Codec\Png\PngImage;

$fn = $argv[1];

echo "Testing $fn\n";
$data = DataBlobInputStream::fromFilename($argv[1]);
$png = PngImage::fromBinary($data);
$im = $png -> toRasterImage();
$im -> write('out/' . basename($argv[1], '.png') . ".ppm");
echo $im -> toBlackAndWhite() -> toString() . "\n";
exit(0);
