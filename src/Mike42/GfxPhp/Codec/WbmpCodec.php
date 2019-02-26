<?php

namespace Mike42\GfxPhp\Codec;

use Exception;
use Mike42\GfxPhp\BlackAndWhiteRasterImage;
use Mike42\GfxPhp\Codec\Common\DataBlobInputStream;
use Mike42\GfxPhp\Codec\ImageDecoder;
use Mike42\GfxPhp\Codec\ImageEncoder;
use Mike42\GfxPhp\RasterImage;

class WbmpCodec implements ImageDecoder, ImageEncoder
{
    protected static $instance = null;

    public function identify(string $blob): string
    {
        $wbmpMagic = substr($blob, 0, 2);
        if ($wbmpMagic == "\x00\x00") {
            // Wireless Application Protocol Bitmap
            return "wbmp";
        }
        return "";
    }

    public function decode(string $blob): RasterImage
    {
        $data = DataBlobInputStream::fromBlob($blob);
        $header = $data -> read(2);
        if($header != "\x00\x00") {
            throw new Exception("Not a WBMP file");
        }
        $width = ord($data -> read(1));
        if($width > 127) {
            throw new Exception("Maximum image width is 127");
        }
        $height = ord($data -> read(1));
        $bytesPerRow = intdiv($width + 7, 8);
        $expectedBytes = $bytesPerRow * $height;
        $binaryData = $data -> read($expectedBytes);
        $dataUnpacked = unpack("C*", $binaryData);
        $dataValues = array_values($dataUnpacked);
        // 1 for white, 0 for black (opposite)
        $image = BlackAndWhiteRasterImage::create($width, $height, $dataValues);
        $image -> invert();
        return $image;

    }

    public function getDecodeFormats(): array
    {
        return ["wbmp"];
    }

    public function encode(RasterImage $image, string $format): string
    {
        $image = $image = $image -> toBlackAndWhite();
        if($image -> getWidth() > 127 || $image -> getHeight() > 127) {
            throw new Exception("Maximum image width or height is 127");
        }
        $image -> invert();
        return "\x00\x00" . chr($image -> getWidth()) . chr($image -> getHeight()) . $image -> getRasterData();
    }

    public function getEncodeFormats(): array
    {
        return ["wbmp"];
    }

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new WbmpCodec();
        }
        return self::$instance;
    }
}