<?php
namespace Mike42\ImagePhp\Codec;

use Mike42\ImagePhp\RasterImage;
use Mike42\ImagePhp\RgbRasterImage;

class PngCodec implements ImageEncoder
{
    protected static $instance = null;

    public function encode(RasterImage $image, string $format): string
    {
        if (!($image instanceof RgbRasterImage)) {
            // Convert if necessary
            $image = $image -> toRgb();
        }
        return $this -> encodeRgb($image);
    }

    public function encodeRgb(RgbRasterImage $image)
    {
        // PNG signature
        $signature = pack("c8", 0x89, 0x50, 0x4e, 0x47, 0x0d, 0x0a, 0x1a, 0x0a);
        // Header chunk
        $width = $image -> getWidth();
        $height = $image -> getHeight();
        $ihdr = $this -> chunk('IHDR', pack('N2C5', $width, $height, 8, 2, 0, 0, 0));
        // Prepend '0' filter method to each scanline
        $scanLines = str_split($image -> getRasterData(), $width * 3);
        $pngRasterData = chr(0) . implode(chr(0), $scanLines);
        $idat = $this -> chunk('IDAT', zlib_encode($pngRasterData, ZLIB_ENCODING_DEFLATE));
        // End chunk
        $iend = $this -> chunk('IEND');
        return $signature . $ihdr . $idat . $iend;
    }

    protected function chunk(string $type, string $data = '')
    {
        $len = strlen($data);
        $lenData = pack("N", $len);
        $bodyData = $type . $data;
        $crcData = pack("N", crc32($bodyData));
        return $lenData . $bodyData . $crcData;
    }

    public function getEncodeFormats(): array
    {
        return ["png"];
    }

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new PngCodec();
        }
        return self::$instance;
    }
}
