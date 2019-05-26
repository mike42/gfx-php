<?php
namespace Mike42\GfxPhp\Codec\Bmp;


use Exception;
use Mike42\GfxPhp\Codec\Common\DataInputStream;
use Mike42\GfxPhp\RasterImage;
use Mike42\GfxPhp\RgbRasterImage;

class BmpFile
{
    const BMP_SIGNATURE = "BM";

    public static function fromBinary(DataInputStream $data) : BmpFile
    {
        // Check header
        $fileType = $data->read(2);
        if(array_search($fileType, ["BM", "BA", "CI", "CP", "IC", "PT", "OS"]) === false) {
            throw new Exception("Not a bitmap image");
        }
        $fileHeader = BmpFileHeader::fromBinary($data);
        $infoHeader = BmpInfoHeader::fromBinary($data);
        // Determine compressed & uncompressed size
        $rowSizeBytes = intdiv(($infoHeader -> bpp * $infoHeader -> width + 31), 32) * 4;
        $uncompressedImgSizeBytes = $rowSizeBytes * $infoHeader -> height;
        if($infoHeader -> compression == BmpInfoHeader::B1_RGB) {
            $compressedImgSizeBytes = $uncompressedImgSizeBytes;
        } else {
            $compressedImgSizeBytes = $infoHeader -> compressedSize;
        }
        // NOTE: may be padding issues here
        print_r($infoHeader);
        $compressedImgData = $data -> read($compressedImgSizeBytes);
        // De-compress if necessary
        switch($infoHeader -> compression) {
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
                throw new \Exception("Compression method not implemented");
            default:
                throw new \Exception("Bad compresson method");
        }
        echo $uncompressedImgData;
        return new BmpFile($fileHeader, $infoHeader, $uncompressedImgData);
    }

    public function toRasterImage() : RasterImage {
        return RgbRasterImage::create(1, 1);
    }
}
