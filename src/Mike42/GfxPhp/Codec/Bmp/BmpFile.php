<?php
namespace Mike42\GfxPhp\Codec\Bmp;

use Exception;
use Mike42\GfxPhp\Codec\Common\DataInputStream;
use Mike42\GfxPhp\RasterImage;
use Mike42\GfxPhp\RgbRasterImage;

class BmpFile
{
    const BMP_SIGNATURE = "BM";

    private $fileHeader;
    private $infoHeader;
    private $uncompressedData;

    public function __construct(BmpFileHeader $fileHeader, BmpInfoHeader $infoHeader, array $data)
    {
        $this -> fileHeader = $fileHeader;
        $this -> infoHeader = $infoHeader;
        $this -> uncompressedData = $data;
    }

    public static function fromBinary(DataInputStream $data) : BmpFile
    {
        // Read two different headers
        $fileHeader = BmpFileHeader::fromBinary($data);
        $infoHeader = BmpInfoHeader::fromBinary($data);
        if ($infoHeader -> bpp != 0 &&
            $infoHeader -> bpp != 1 &&
            $infoHeader -> bpp != 4 &&
            $infoHeader -> bpp != 8 &&
            $infoHeader -> bpp != 16 &&
            $infoHeader -> bpp != 24 &&
            $infoHeader -> bpp != 32) {
            throw new Exception("Bit depth " . $infoHeader -> bpp . " not valid.");
        } else if ($infoHeader -> bpp != 24) {
            // Fail early to give a clearer error for the things which aren't tested yet
            throw new Exception("Bit depth " . $infoHeader -> bpp . " not implemented.");
        }
        // Skip color table (allowed in a true color image, but not useful)
        if ($infoHeader -> colors > 0) {
            // for non-truecolor images, 0 will mean 2^bpp.
            // Size of each entry may also be variable
            $data -> read($infoHeader -> colors * 4);
        }
        // Determine compressed & uncompressed size
        $rowSizeBytes = intdiv(($infoHeader -> bpp * $infoHeader -> width + 31), 32) * 4;
        $uncompressedImgSizeBytes = $rowSizeBytes * $infoHeader -> height;
        if ($infoHeader -> compression == BmpInfoHeader::B1_RGB) {
            $compressedImgSizeBytes = $uncompressedImgSizeBytes;
        } else {
            $compressedImgSizeBytes = $infoHeader -> compressedSize;
        }
        $compressedImgData = $data -> read($compressedImgSizeBytes);
        // De-compress if necessary
        switch ($infoHeader -> compression) {
            case BmpInfoHeader::B1_RGB:
                $uncompressedImgData = $compressedImgData;
                break;
            case BmpInfoHeader::B1_RLE8:
            case BmpInfoHeader::B1_RLE4:
            case BmpInfoHeader::B1_BITFILEDS:
            case BmpInfoHeader::B1_JPEG:
            case BmpInfoHeader::B1_PNG:
            case BmpInfoHeader::B1_ALPHABITFIELDS:
            case BmpInfoHeader::B1_CMYK:
            case BmpInfoHeader::B1_CMYKRLE8:
            case BmpInfoHeader::B1_CMYKRLE4:
                throw new Exception("Compression method not implemented");
            default:
                throw new Exception("Bad compression method");
        }
        // Account for padding, row order
        $paddedLines = str_split($uncompressedImgData, $rowSizeBytes);
        $dataLines = [];
        $rowDataBytes = intdiv($infoHeader -> bpp * $infoHeader -> width + 7, 8); // Excludes padding bytes
        for ($i = count($paddedLines) - 1; $i >= 0; $i--) { // Iterate lines backwards
            $dataLines[] = substr($paddedLines[$i], 0, $rowDataBytes);
        }
        $uncompressedImgData = implode("", $dataLines);
        // Account for RGB vs BGR in file format
        if ($infoHeader -> bpp == 24) {
            $pixels = str_split($uncompressedImgData, 3);
            array_walk($pixels, ["\\Mike42\\GfxPhp\\Codec\\Bmp\\BmpFile", "transformRevString"]);
            $uncompressedImgData = implode("", $pixels);
        }
        // Convert to array of numbers 0-255.
        $dataArray = array_values(unpack("c*", $uncompressedImgData));
        return new BmpFile($fileHeader, $infoHeader, $dataArray);
    }

    public function toRasterImage() : RasterImage
    {
        if ($this -> infoHeader -> bpp == 24) {
            return RgbRasterImage::create($this -> infoHeader -> width, $this -> infoHeader -> height, $this -> uncompressedData);
        }
        throw new Exception("Unknown bit depth " . $this -> infoHeader -> bpp);
    }

    public static function transformRevString(&$item, $key)
    {
        // Convert RGB to BGR
        $item = strrev($item);
    }
}
