<?php
namespace Mike42\ImagePhp\Codec;

use Mike42\ImagePhp\RasterImage;

interface ImageEncoder
{
    public function getEncodeFormats() : array;

    public function encode(RasterImage $image) : string;
}
