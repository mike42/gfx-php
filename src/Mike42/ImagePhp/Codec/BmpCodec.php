<?php

namespace Mike42\ImagePhp\Codec;

use Mike42\ImagePhp\RasterImage;
use Mike42\ImagePhp\RgbRasterImage;

class BmpCodec implements ImageEncoder
{
    protected static $instance = null;

    public function encode(RasterImage $image, string $format): string
    {
        if (!($image instanceof RgbRasterImage)) {
            // Convert if necessary
            $image = $image -> toRgb();
        }
        // Output uncompressed 24 bit BMP file
        $header = pack(
            "nV3",
            0x424d, // 'BM' magic number
            0, // File size
            0, // Reserved
            0
        ); // Offset
        $width = $image -> getWidth();
        $height = $image -> getHeight();
        $infoHeader = pack(
            "V3v2V6",
            40,
            $width, // Width
            $height, // Height
            1, // Planes
            24, // bpp
            0, // Compression (none)
            0, // Image size compressed
            1, // Horizontal res
            1, // Vertical res
            0, // Number of colors
            0
        ); // Number of important colors
        $colorTable = "";
        // Transform RGB ordering to BGR ordering
        $pixels = str_split($image -> getRasterData(), 3);
        array_walk($pixels, [$this, "transformRevString"]);
        $rasterData = implode("", $pixels);
        // Transform top-down unpadded lines to bottom-up padded lines
        $originalWidth = $width * 3;
        $paddingLength = (4 - ($originalWidth & 3)) & 3;
        $padding = str_repeat("\x00", $paddingLength);
        $lines = str_split($rasterData, $originalWidth);
        $lines = array_reverse($lines, false);
        $pixelData = implode($padding, $lines) . $padding;
        // Return bitmap & header
        return $header . $infoHeader . $colorTable . $pixelData;
    }

    protected function transformRevString(&$item, $key)
    {
        $item = strrev($item);
    }
    
    public function getEncodeFormats(): array
    {
        return ["bmp", "dib"];
    }

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new BmpCodec();
        }
        return self::$instance;
    }
}
