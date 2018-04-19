<?php

namespace Mike42\ImagePhp\Codec;

use Mike42\ImagePhp\RasterImage;
use Exception;
use Mike42\ImagePhp\BlackAndWhiteRasterImage;
use Mike42\ImagePhp\GrayscaleRasterImage;
use Mike42\ImagePhp\RgbRasterImage;

class PnmCodec implements ImageDecoder, ImageEncoder
{
    protected static $instance = null;

    public function identify(string $blob): string
    {
        $pnmMagic = substr($blob, 0, 2);
        if ($pnmMagic == "P1" || $pnmMagic == "P4") {
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
        if ($im_hdr_line !== "P4\n" &&
            $im_hdr_line !== "P5\n" &&
            $im_hdr_line !== "P6\n") {
            throw new Exception("Format not supported. Expected PNM bitmap.");
        }
        $pnmMagicNumber = substr($im_hdr_line, 0, 2);
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
        // Extract data and return differently based on each magic number.
        switch ($pnmMagicNumber) {
            case "P4":
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
            case "P5":
                // Determine color depth
                $line_end = self::skipComments($blob, $line_end);
                $next_line_end = strpos($blob, "\n", $line_end + 1);
                $maxValLine = substr($blob, $line_end + 1, ($next_line_end - $line_end) - 1);
                $maxVal = (int)$maxValLine;
                $depth = $maxVal >= 255 ? 2 : 1;
                $line_end = $next_line_end;
                // Extract data
                $expectedBytes = $width * $height * $depth;
                $data = substr($blob, $line_end + 1);
                $actualBytes = strlen($data);
                if ($expectedBytes != $actualBytes) {
                    throw new Exception("Expected $expectedBytes data, but got $actualBytes, file probably corrupt.");
                }
                if ($depth == 2) {
                    $dataUnpacked = unpack("n*", $data);
                } else {
                    $dataUnpacked = unpack("C*", $data);
                }
                $dataValues = array_values($dataUnpacked);
                return GrayscaleRasterImage::create($width, $height, $dataValues, $maxVal);
            case "P6":
                $depth = 1;
                $expectedBytes = $width * $height * $depth * 3;
                $data = substr($blob, $line_end + 1);
                $actualBytes = strlen($data);
                if ($expectedBytes != $actualBytes) {
                    throw new Exception("Expected $expectedBytes data, but got $actualBytes, file probably corrupt.");
                }
                $dataUnpacked = unpack("C*", $data);
                $dataValues = array_values($dataUnpacked);
                return RgbRasterImage::create($width, $height, $maxVal, $dataValues);
        }
        // TODO handle formats in a way that lets us remove this fallthrough.
        throw new Exception("Format not supported.");
    }
    
    protected function pbmBinary()
    {
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
        if ($image instanceof BlackAndWhiteRasterImage) {
            $dimensions = $image -> getWidth() . " " . $image -> getHeight();
            $data = $image -> getRasterData();
            $contents = "P4\n$dimensions\n$data";
            return $contents;
        } else if ($image instanceof GrayscaleRasterImage) {
            $dimensions = $image -> getWidth() . " " . $image -> getHeight();
            $maxVal = $image -> getMaxVal();
            $data = $image -> getRasterData();
            $contents = "P5\n$dimensions\n$maxVal\n$data";
            return $contents;
        }
        throw new Exception("Unsupported image type");
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
