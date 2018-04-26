<?php
namespace Mike42\ImagePhp\Codec;

use Mike42\ImagePhp\GrayscaleRasterImage;
use Mike42\ImagePhp\RasterImage;
use Mike42\ImagePhp\Util\LzwCompression;

class GifCodec implements ImageEncoder
{
    protected static $instance = null;

    public function encode(RasterImage $image, string $format): string
    {
        if (!($image instanceof GrayscaleRasterImage)) {
            // Convert if necessary
            $image = $image -> toGrayscale();
        } else {
            if ($image -> getMaxVal() != 256) {
                // Scaling has the side-effect of mapping to 256 colors
                $image = $image -> scale($image -> getWidth(), $image -> getHeight());
            }
        }
        // GIF signature
        $signature = pack("c6", 0x47, 0x49, 0x46, 0x38, 0x39, 0x61);
        // Header chunk
        $width = $image -> getWidth();
        $height = $image -> getHeight();
        $header = pack('v2c3', $width, $height, 0xF7, 0, 0);
        // Color table of grayscale
        $colorTable = [];
        for ($i = 0; $i < 256; $i++) {
            $colorTable[] = $i;
            $colorTable[] = $i;
            $colorTable[] = $i;
        }
        $gct = pack("C*", ... $colorTable);
        // Graphic control
        $gce = pack("C4vC2", 0x21, 0xF9, 0x04, 0x01, 0x00, 0x10, 0x00);
        // Image
        $imageDescriptor = pack('Cv4C', 0x2C, 0, 0, $width, $height, 0);
        $raster = $image -> getRasterData();
        $compressedData = LzwCompression::compress($raster, 0x08);
        // Field testing the corresponding decoder, which we don't use anywhere else.
        // It's a good start for GIF decoding if we can at least read our own LZW back.
        $decompressedData = LzwCompression::decompress($compressedData, 0x08);
        if ($raster !== $decompressedData) {
            throw new \Exception("Failed to read back the generated LZW data.");
        }
         $slices = str_split($compressedData, 255);
         $imageData = chr(0x08);
        foreach ($slices as $slice) {
            $imageData .= chr(strlen($slice)) . $slice;
        }
         $imageData .= chr(0);
        $terminator = pack("C", 0x3B);
        return $signature . $header . $gct . $gce . $imageDescriptor . $imageData . $terminator;
    }

    public function getEncodeFormats(): array
    {
        return ["gif"];
    }

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new GifCodec();
        }
        return self::$instance;
    }
}
