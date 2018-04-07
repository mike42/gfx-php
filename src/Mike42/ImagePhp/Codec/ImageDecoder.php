<?php
namespace Mike42\ImagePhp\Codec;

use Mike42\ImagePhp\RasterImage;

interface ImageDecoder
{
    public function getDecodeFormats() : array;

    public function identify(string $blob) : string;

    public function decode(string $blob) : RasterImage;
}
