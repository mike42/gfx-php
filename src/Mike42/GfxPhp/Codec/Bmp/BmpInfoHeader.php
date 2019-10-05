<?php
namespace Mike42\GfxPhp\Codec\Bmp;

use Exception;
use Mike42\GfxPhp\Codec\Common\DataInputStream;

class BmpInfoHeader
{
    const BITMAPCOREHEADER_SIZE = 12;
    const OS21XBITMAPHEADER_SIZE = 12;
    const BITMAPINFOHEADER_SIZE = 40;
    const BITMAPV2INFOHEADER_SIZE = 52;
    const BITMAPV3INFOHEADER_SIZE = 56;
    const BITMAPV4HEADER_SIZE = 108;
    const BITMAPV5HEADER_SIZE = 124;

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
    public $horizontalRes;
    public $importantColors;
    public $planes;
    public $verticalRes;
    public $width;
    public $redMask;
    public $greenMask;
    public $blueMask;
    public $alphaMask;

    public function __construct(
        int $headerSize,
        int $width,
        int $height,
        int $planes,
        int $bpp,
        int $compression = 0,
        int $compressedSize = 0,
        int $horizontalRes = 0,
        int $verticalRes = 0,
        int $colors = 0,
        int $importantColors = 0,
        int $redMask = 0,
        int $greenMask = 0,
        int $blueMask = 0,
        int $alphaMask = 0,
        int $csType = 0,
        array $endpoint = [],
        array $gamma = []
    ) {
        $this -> headerSize = $headerSize;
        $this -> width = $width;
        // Not possible to read signed little-endian 32-bit long with unpack(), but height may be negative.
        $this -> height = ($height >= (2**31)) ? ($height - (2**32)) : $height;
        $this -> planes = $planes;
        $this -> bpp = $bpp;
        $this -> compression = $compression;
        $this -> compressedSize = $compressedSize;
        $this -> horizontalRes = $horizontalRes;
        $this -> verticalRes = $verticalRes;
        $this -> colors = $colors;
        $this -> importantColors = $importantColors;
        $this -> redMask = $redMask;
        $this -> greenMask = $greenMask;
        $this -> blueMask = $blueMask;
        $this -> alphaMask = $alphaMask;
    }

    public static function fromBinary(DataInputStream $data) : BmpInfoHeader
    {
        $infoHeaderSizeData = $data -> read(4);
        $infoHeaderSize = unpack("V", $infoHeaderSizeData)[1];
        switch ($infoHeaderSize) {
            case self::BITMAPCOREHEADER_SIZE:
                return self::readCoreHeader($data);
            case 64:
                return self::readOs22xBitmapHeader($data);
            case 16:
                throw new Exception("OS22XBITMAPHEADER not implemented");
            case self::BITMAPINFOHEADER_SIZE:
                return self::readBitmapInfoHeader($data);
            case self::BITMAPV2INFOHEADER_SIZE:
                return self::readBitmapV2InfoHeader($data);
            case self::BITMAPV3INFOHEADER_SIZE:
                return self::readBitmapV3InfoHeader($data);
            case self::BITMAPV4HEADER_SIZE:
                return self::readBitmapV4Header($data);
            case self::BITMAPV5HEADER_SIZE:
                return self::readBitmapV5Header($data);
            default:
                throw new Exception("Info header size " . $infoHeaderSize . " is not supported.");
        }
    }

    private static function readCoreHeader(DataInputStream $data) : BmpInfoHeader
    {
        $infoData = $data -> read(self::BITMAPCOREHEADER_SIZE - 4);
        $fields = unpack("vwidth/vheight/vplanes/vbpp", $infoData);
        return new BmpInfoHeader(
            self::BITMAPCOREHEADER_SIZE,
            $fields['width'],
            $fields['height'],
            $fields['planes'],
            $fields['bpp']
        );
    }

    private static function getInfoFields(DataInputStream $data) : array
    {
        $infoData = $data -> read(self::BITMAPINFOHEADER_SIZE - 4);
        return unpack("Vwidth/Vheight/vplanes/vbpp/Vcompression/VcompressedSize/VhorizontalRes/VverticalRes/Vcolors/VimportantColors", $infoData);
    }

    private static function getV2fields(DataInputStream $data) : array
    {
        $infoData = $data -> read(self::BITMAPV2INFOHEADER_SIZE - self::BITMAPINFOHEADER_SIZE);
        return unpack("VredMask/VgreenMask/VblueMask", $infoData);
    }

    private static function getV3fields(DataInputStream $data) : array
    {
        $infoData = $data -> read(self::BITMAPV3INFOHEADER_SIZE - self::BITMAPV2INFOHEADER_SIZE);
        return unpack("ValphaMask", $infoData);
    }

    private static function getV4fields(DataInputStream $data) : array
    {
        // color space
        $csTypeData = $data -> read(4);
        $csType = unpack("VcsType", $csTypeData);
        // endpoint
        $csEndpoints = [];
        foreach (['red', 'green', 'blue'] as $channel) {
            $channelData = $data -> read(12);
            $csEndpoints[$channel] = unpack('Vx/Vy/Vz', $channelData);
        }
        // gamma
        $gammaData = $data -> read(12);
        $gamma =  unpack("Vred/Vgreen/Vblue", $gammaData);
        return [
            'csType' => $csType['csType'],
            'endpoint' => $csEndpoints,
            'gamma' => $gamma
        ];
    }

    private static function getV5fields(DataInputStream $data) : array
    {
        $infoData = $data -> read(self::BITMAPV5HEADER_SIZE - self::BITMAPV4HEADER_SIZE);
        return unpack("Vintent/VprofileData/VprofileSize/Vreserved", $infoData);
    }

    private static function readBitmapInfoHeader(DataInputStream $data) : BmpInfoHeader
    {
        $infoFields = self::getInfoFields($data);
        // Quirk- A BITMAPINFOHEADER specifying B1_BITFIELDS has 12 bytes of masks after it.
        // In later versions, this information is part of the header itself, and is read unconditionally.
        $redMask = 0;
        $greenMask = 0;
        $blueMask = 0;
        $alphaMask = 0;
        if ($infoFields['compression'] === self::B1_BITFILEDS || $infoFields['compression'] === self::B1_ALPHABITFIELDS) {
            // Quirk!
            $rgbMaskFields = self::getV2fields($data);
            $redMask = $rgbMaskFields['redMask'];
            $greenMask = $rgbMaskFields['greenMask'];
            $blueMask = $rgbMaskFields['blueMask'];
        }
        if ($infoFields['compression'] === self::B1_ALPHABITFIELDS) {
            // we might or might not need to read a 4-byte alpha mask too, depending on the compression type.
            $alphaMaskFields = self::getV3fields($data);
            $alphaMask = $alphaMaskFields['alphaMask'];
        }
        return new BmpInfoHeader(
            self::BITMAPINFOHEADER_SIZE,
            $infoFields['width'],
            $infoFields['height'],
            $infoFields['planes'],
            $infoFields['bpp'],
            $infoFields['compression'],
            $infoFields['compressedSize'],
            $infoFields['horizontalRes'],
            $infoFields['verticalRes'],
            $infoFields['colors'],
            $infoFields['importantColors'],
            $redMask,
            $greenMask,
            $blueMask,
            $alphaMask
        );
    }

    private static function readBitmapV2InfoHeader(DataInputStream $data) : BmpInfoHeader
    {
        $infoFields = self::getInfoFields($data);
        $v2fields = self::getV2fields($data);
        return new BmpInfoHeader(
            self::BITMAPV2INFOHEADER_SIZE,
            $infoFields['width'],
            $infoFields['height'],
            $infoFields['planes'],
            $infoFields['bpp'],
            $infoFields['compression'],
            $infoFields['compressedSize'],
            $infoFields['horizontalRes'],
            $infoFields['verticalRes'],
            $infoFields['colors'],
            $infoFields['importantColors'],
            $v2fields['redMask'],
            $v2fields['greenMask'],
            $v2fields['blueMask']
        );
    }

    private static function readBitmapV3InfoHeader(DataInputStream $data) : BmpInfoHeader
    {
        $infoFields = self::getInfoFields($data);
        $v2fields = self::getV2fields($data);
        $v3fields = self::getV3fields($data);
        return new BmpInfoHeader(
            self::BITMAPV3INFOHEADER_SIZE,
            $infoFields['width'],
            $infoFields['height'],
            $infoFields['planes'],
            $infoFields['bpp'],
            $infoFields['compression'],
            $infoFields['compressedSize'],
            $infoFields['horizontalRes'],
            $infoFields['verticalRes'],
            $infoFields['colors'],
            $infoFields['importantColors'],
            $v2fields['redMask'],
            $v2fields['greenMask'],
            $v2fields['blueMask'],
            $v3fields['alphaMask']
        );
    }

    private static function readBitmapV4Header(DataInputStream $data) : BmpInfoHeader
    {
        // https://docs.microsoft.com/en-us/windows/win32/api/wingdi/ns-wingdi-bitmapv4header
        $infoFields = self::getInfoFields($data);
        $v2fields = self::getV2fields($data);
        $v3fields = self::getV3fields($data);
        $v4fields = self::getV4fields($data);
        return new BmpInfoHeader(
            self::BITMAPV4HEADER_SIZE,
            $infoFields['width'],
            $infoFields['height'],
            $infoFields['planes'],
            $infoFields['bpp'],
            $infoFields['compression'],
            $infoFields['compressedSize'],
            $infoFields['horizontalRes'],
            $infoFields['verticalRes'],
            $infoFields['colors'],
            $infoFields['importantColors'],
            $v2fields['redMask'],
            $v2fields['greenMask'],
            $v2fields['blueMask'],
            $v3fields['alphaMask'],
            $v4fields['csType'],
            $v4fields['endpoint'],
            $v4fields['gamma']
        );
    }

    private static function readBitmapV5Header(DataInputStream $data) : BmpInfoHeader
    {
        // Structure documented @ https://docs.microsoft.com/en-us/windows/win32/api/wingdi/ns-wingdi-bitmapv5header
        $infoFields = self::getInfoFields($data);
        $v2fields = self::getV2fields($data);
        $v3fields = self::getV3fields($data);
        $v4fields = self::getV4fields($data);
        $v5fields = self::getV5fields($data);
        if ($v5fields['profileSize'] > 0) {  // TODO include these fields
            throw new Exception("Bitmaps with embedded ICC profile data are not supported.");
        }
        return new BmpInfoHeader(
            self::BITMAPV5HEADER_SIZE,
            $infoFields['width'],
            $infoFields['height'],
            $infoFields['planes'],
            $infoFields['bpp'],
            $infoFields['compression'],
            $infoFields['compressedSize'],
            $infoFields['horizontalRes'],
            $infoFields['verticalRes'],
            $infoFields['colors'],
            $infoFields['importantColors'],
            $v2fields['redMask'],
            $v2fields['greenMask'],
            $v2fields['blueMask'],
            $v3fields['alphaMask'],
            $v4fields['csType'],
            $v4fields['endpoint'],
            $v4fields['gamma']
        );
    }

    private static function readOs22xBitmapHeader(DataInputStream $data)
    {
        throw new Exception("OS22XBITMAPHEADER not implemented");
    }
}
