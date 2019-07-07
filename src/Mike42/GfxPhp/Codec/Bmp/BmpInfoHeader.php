<?php
namespace Mike42\GfxPhp\Codec\Bmp;

use Exception;
use Mike42\GfxPhp\Codec\Common\DataInputStream;

class BmpInfoHeader
{
    const BITMAPCOREHEADER_SIZE = 12;
    const OS21XBITMAPHEADER_SIZE = 12;
    const BITMAPINFOHEADER_SIZE = 40;

    const B1_RGB = 0;
    const B1_RLE8 = 1;
    const B1_RLE4 = 2;
    const B1_BITFILEDS = 3;
    const B1_JPEG = 4;
    const B1_PNG = 5;
    const B1_ALPHABITFIELDS = 6;
    const B1_CMYK = 11;
    const B1_CMYKRLE8 = 12;
    const B1_CMYKRLE4 = 13;

    public $bpp;
    public $colors;
    public $compressedSize;
    public $compression;
    public $headerSize;
    public $height;
    public $hprizontalRes;
    public $importantColors;
    public $planes;
    public $verticalRes;
    public $width;

    public function __construct(int $headerSize, int $width, int $height, int $planes, int $bpp, int $compression = 0, int $compressedSize = 0, int $horizontalRes = 0, int $verticalRes = 0, int $colors = 0, int $importantColors = 0) {
        $this -> headerSize = $headerSize;
        $this -> width = $width;
        $this -> height = $height;
        $this -> planes = $planes;
        $this -> bpp = $bpp;
        $this -> compression = $compression;
        $this -> compressedSize = $compressedSize;
        $this -> hprizontalRes = $horizontalRes;
        $this -> verticalRes = $verticalRes;
        $this -> colors = $colors;
        $this -> importantColors = $importantColors;
    }

    public static function fromBinary(DataInputStream $data) : BmpInfoHeader
    {
        $infoHeaderSizeData = $data -> read(4);
        $infoHeaderSize = unpack("V", $infoHeaderSizeData)[1];
        switch($infoHeaderSize) {
            case self::BITMAPCOREHEADER_SIZE;
                return self::readCoreHeader($data);
            case 64:
                throw new Exception("OS22XBITMAPHEADER not implemented");
            case 16:
                throw new Exception("OS22XBITMAPHEADER not implemented");
            case self::BITMAPINFOHEADER_SIZE:
                return self::readBitmapInfoHeader($data);
            case 52:
                throw new Exception("BITMAPV2INFOHEADER not implemented");
            case 56:
                throw new Exception("BITMAPV3INFOHEADER not implemented");
            case 108:
                throw new Exception("BITMAPV4HEADER not implemented");
            case 124:
                throw new Exception("BITMAPV5HEADER not implemented");
            default:
                throw new Exception("Info header size " . $infoHeaderSize . " is not supported.");
        }
    }

    private static function readCoreHeader(DataInputStream $data) : BmpInfoHeader
    {
        $infoData = $data -> read(self::BITMAPCOREHEADER_SIZE - 4);
        $fields = unpack("vwidth/vheight/vplanes/vbpp", $infoData);
        return new BmpInfoHeader(self::BITMAPCOREHEADER_SIZE, $fields['width'], $fields['height'], $fields['planes'], $fields['bpp']);
    }

    private static function readBitmapInfoHeader(DataInputStream $data) : BmpInfoHeader
    {
        $infoData = $data -> read(self::BITMAPINFOHEADER_SIZE - 4);
        $fields = unpack("Vwidth/Vheight/vplanes/vbpp/Vcompression/VcompressedSize/VhorizontalRes/VverticalRes/Vcolors/VimportantColors", $infoData);
        return new BmpInfoHeader(self::BITMAPINFOHEADER_SIZE, $fields['width'], $fields['height'], $fields['planes'], $fields['bpp'], $fields['compression'], $fields['compressedSize'], $fields['horizontalRes'], $fields['verticalRes'], $fields['colors'], $fields['importantColors']);
    }
}