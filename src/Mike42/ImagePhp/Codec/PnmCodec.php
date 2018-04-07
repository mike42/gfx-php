<?php

namespace Mike42\ImagePhp\Codec;

use Mike42\ImagePhp\RasterImage;
use Exception;
use Mike42\ImagePhp\BlackAndWhiteRasterImage;

class PnmCodec implements ImageDecoder, ImageEncoder
{
    protected static $instance = null;

    public function identify(string $blob): string
    {
        $pbmMagicNumber = substr($blob, 0, 2);
        if ($pnmMagic == "P4" || $pnmMagic == "P1") {
            // Portable BitMap
            return "image/x‑portable‑bitmap";
        } else if ($pnmMagic == "P2" || $pnmMagic == "P5") {
            // Portable GrayMap
            return "image/x‑portable‑graymap";
        } else if ($pnmMagic == "P3" || $pnmMagic == "P6") {
            // Portable PixMap
            return "image/x‑portable‑pixmap";
        }
        return null;
    }

    public function decode(string $blob): RasterImage
    {
        // Read header line
        $im_hdr_line = substr($blob, 0, 3);
        if ($im_hdr_line !== "P4\n") {
            throw new Exception("Format not supported. Expected P4 bitmap.");
        }
        // Skip comments
        $line_end = self::skipComments($blob, 2);
        // Read image size
        $next_line_end = strpos($blob, "\n", $line_end + 1);
        if ($next_line_end === false) {
            throw new Exception("Unexpected end of file, probably corrupt.");
        }
        $size_line = substr($blob, $line_end + 1, ($next_line_end - $line_end) - 1);
        $sizes = explode(" ", $size_line);
        if (count($sizes) != 2 || !is_numeric($sizes[0]) || !is_numeric($sizes[1])) {
            throw new Exception("Image size is bogus, file probably corrupt.");
        }
        $width = $sizes[0];
        $height = $sizes[1];
        $line_end = $next_line_end;
        // Skip comments again
        $line_end = self::skipComments($blob, $line_end);
        // Extract data..
        $bytesPerRow = intdiv($width + 7, 8);
        $expectedBytes = $bytesPerRow * $height;
        $data = substr($blob, $line_end + 1);
        $actualBytes = strlen($data);
        if ($expectedBytes != $actualBytes) {
            throw new Exception("Expected $expectedBytes data, but got $actualBytes, file probably corrupt.");
        }
        $dataUnpacked = unpack("C*", $data);
        $dataValues = array_values($dataUnpacked);
        return BlackAndWhiteRasterImage::create($width, $height, $dataValues);
    }

    public function getDecodeFormats(): array
    {
        return ["image/x‑portable‑bitmap", "image/x‑portable‑graymap", "image/x‑portable‑pixmap"];
    }

    protected static function skipComments(string $im_data, int $line_end) : int
    {
        while ($line_end !== false && substr($im_data, $line_end + 1, 1) == "#") {
            $line_end = strpos($im_data, "\n", $line_end + 1);
        }
        if ($line_end === false) {
            throw new Exception("Unexpected end of file, probably corrupt.");
        }
        return $line_end;
    }

    public function encode(RasterImage $image): string
    {
        $dimensions = $image -> getWidth() . " " . $image -> getHeight();
        $data = $image -> getRasterData();
        $contents = "P4\n$dimensions\n$data";
        return $contents;
    }

    public function getEncodeFormats(): array
    {
        return ["image/x‑portable‑bitmap", "image/x‑portable‑graymap", "image/x‑portable‑pixmap"];
    }

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new PnmCodec();
        }
        return self::$instance;
    }
}
