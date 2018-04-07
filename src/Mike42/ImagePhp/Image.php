<?php

namespace Mike42\ImagePhp;

use Mike42\ImagePhp\Codec\ImageCodec;

class Image
{
    // Color depths
    public const IMAGE_BLACK_WHITE = 1;
    public const IMAGE_GRAY = 2;
    public const IMAGE_RGB = 3;
    public const IMAGE_RGBA = 4;

    protected static $codecs = null;
    
    public static function fromFile(string $filename) : RasterImage
    {
        $blob = file_get_contents($filename);
        if ($blob === false) {
            throw new \Exception("Could not retrieve image data from '$filename'. Check that the file exists and can be read.");
        }
        return self::fromBlob($blob);
    }
    
    public static function fromBlob(string $blob, string $filename = null) : RasterImage
    {
        if (self::$codecs === null) {
            self::$codecs = ImageCodec::getInstance();
        }
        $format = self::$codecs -> identify($blob);
        if ($format == null) {
            throw new \Exception("Unknown format for image '$filename'.");
        }
        $decoder = self::$codecs ->getDecoderForFormat($format);
        if ($decoder == null) {
            throw new \Exception("Format $format not supported, reading '$filename'.");
        }
        return $decoder -> decode($blob);
    }
    
    public function create(int $width, int $height, int $impl = self::IMAGE_BLACK_WHITE)
    {
        return BlackAndWhiteRasterImage::create($width, $height);
    }
}
