<?php
namespace Mike42\GfxPhp\Codec\Bmp;

use Exception;
use Mike42\GfxPhp\Codec\Common\DataInputStream;
use Mike42\GfxPhp\Codec\Png\PngImage;
use Mike42\GfxPhp\IndexedRasterImage;
use Mike42\GfxPhp\RasterImage;
use Mike42\GfxPhp\RgbRasterImage;

class BmpFile
{
    const BMP_SIGNATURE = "BM";

    private $fileHeader;
    private $infoHeader;
    private $palette;
    private $uncompressedData;

    public function __construct(BmpFileHeader $fileHeader, BmpInfoHeader $infoHeader, array $data, array $palette)
    {
        $this -> fileHeader = $fileHeader;
        $this -> infoHeader = $infoHeader;
        $this -> uncompressedData = $data;
        $this -> palette = $palette;
    }

    public static function fromBinary(DataInputStream $data) : BmpFile
    {
        // Read two different headers
        $fileHeader = BmpFileHeader::fromBinary($data);
        $infoHeader = BmpInfoHeader::fromBinary($data);
        if ($infoHeader -> bpp != 0 &&
            $infoHeader -> bpp != 1 &&
            $infoHeader -> bpp != 2 &&
            $infoHeader -> bpp != 4 &&
            $infoHeader -> bpp != 8 &&
            $infoHeader -> bpp != 16 &&
            $infoHeader -> bpp != 24 &&
            $infoHeader -> bpp != 32) {
            throw new Exception("Bit depth " . $infoHeader -> bpp . " not valid.");
        } else if ($infoHeader -> bpp === 0 ||
            $infoHeader -> bpp === 16 ||
            $infoHeader -> bpp === 32) {
            // Fail early to give a clearer error for the things which aren't tested yet
            throw new Exception("Bit depth " . $infoHeader -> bpp . " not implemented.");
        }
        // See how many colors we expect. 2^n colors in table for bpp <= 8, 0 for higher color depths
        $colorCount = $infoHeader -> bpp <= 8 ? 2 **  $infoHeader -> bpp : 0;
        if ($infoHeader -> colors > 0) {
            // .. unless otherwise specified
            $colorCount = $infoHeader -> colors;
        }
        $colorTable = [];
        for ($i = 0; $i < $colorCount; $i++) {
            $entryData = $data -> read(4);
            $color = unpack("C*", $entryData);
            $colorTable[] = [$color[3], $color[2], $color[1]];
        }
        // May need to skip here if header shows pixel data later than we expect
        // Determine compressed & uncompressed size
        $rowSizeBytes = intdiv(($infoHeader -> bpp * $infoHeader -> width + 31), 32) * 4;
        $uncompressedImgSizeBytes = $rowSizeBytes * $infoHeader -> height;
        if ($infoHeader -> compression == BmpInfoHeader::B1_RGB) {
            $compressedImgSizeBytes = $uncompressedImgSizeBytes;
        } else {
            $compressedImgSizeBytes = $infoHeader -> compressedSize;
            // Limit height to prevent insane allocations during decompression if file is corrupt
            if ($infoHeader -> width > 65535 || $infoHeader -> height > 65535 || $infoHeader -> width < 0 || $infoHeader -> height < 0) {
                throw new Exception("Image size " . $infoHeader -> width . "x" . $infoHeader -> height . " is outside the supported range.");
            }
        }
        $compressedImgData = $data -> read($compressedImgSizeBytes);
        // De-compress if necessary
        switch ($infoHeader -> compression) {
            case BmpInfoHeader::B1_RGB:
                $uncompressedImgData = $compressedImgData;
                break;
            case BmpInfoHeader::B1_RLE8:
                if ($infoHeader -> bpp !== 8) {
                    throw new Exception("RLE8 compression only valid for 8-bit images");
                }
                $uncompressedImgData = self::rle8decode($compressedImgData, $infoHeader -> width, $infoHeader -> height);
                $actualSize = strlen($uncompressedImgData);
                if ($uncompressedImgSizeBytes !== $actualSize) {
                    throw new Exception("RLE8 decode failed. Expected $uncompressedImgSizeBytes bytes uncompressed, got $actualSize");
                }
                break;
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
        $dataArray = array_values(unpack("C*", $uncompressedImgData));
        if (!$data -> isEof()) {
            throw new Exception("BMP image has unexpected trailing data");
        }
        return new BmpFile($fileHeader, $infoHeader, $dataArray, $colorTable);
    }

    private static function rle8decode(string $compressedImgData, int $width, int $height)
    {
        // Padding to 4-byte boundary: Can this be simplified?
        $rowWidth = intdiv((8 * $width + 31), 32) * 4;
        // Initialize buffer to 0's
        $outpNum = [];
        for ($y = 0; $y < $height; $y++) {
            $outpNum[] = array_fill(0, $rowWidth, 0);
        }
        // read input data into 2d buffer
        $inpNum = array_values(unpack("C*", $compressedImgData));
        $x = 0;
        $y = 0;
        $i = 0;
        $len = intdiv(count($inpNum), 2) * 2;
        while ($i < $len) {
            $firstByte = $inpNum[$i];
            $secondByte = $inpNum[$i + 1];
            $i += 2;
            //echo "Pair $firstByte $secondByte\n";
            if ($firstByte === 0) {
                if ($secondByte == 0) {
                    //echo "End of line\n";
                    // EOL
                    $x = 0;
                    $y++;
                    if ($y >= $height) {
                        //echo "ERROR Y overflow on EOL, breaking.";
                        break;
                    }
                } else if ($secondByte == 1) {
                    //echo "End of bitmap\n";
                    // End of bitmap
                    break;
                } else if ($secondByte == 2) {
                    if ($i >= $len) {
                        throw new Exception("Unexpected EOF");
                    }
                    $firstDeltaByte = $inpNum[$i];
                    $secondDeltaByte = $inpNum[$i + 1];
                    //echo "Delta $firstDeltaByte, $secondDeltaByte\n";
                    $i += 2;
                    $x += $firstDeltaByte;
                    $y += $secondDeltaByte;
                    if ($x == $width) {
                        $x = 0;
                        //$y++;
                        // echo "Wrapping line x=$x, y=$y\n";
                    } else if ($x > $width) {
                        throw new Exception("Overflow.");
                    }
                    if ($y >= $height) {
                        // Overflow
                        //echo "ERROR Y overflow on jump, breaking.";
                        throw new Exception("Bitmap compressed data exceeds image boundary; file is not valid.");
                    }
                } else {
                    //echo "Absolute run of $secondByte bytes\n";
                    $absoluteLen = $secondByte;
                    for ($j = 0; $j < $absoluteLen; $j++) {
                        //echo "Setting byte " . $inpNum[$i + $j] . " x=$x, y=$y (j=$j)\n";
                        $outpNum[$y][$x] = $inpNum[$i + $j];
                        $x++;
                        if ($x == $width) {
                            $x = 0;
                            //$y++;
                            // echo "Wrapping line x=$x, y=$y\n";
                        } else if ($x > $width) {
                            throw new Exception("Overflow..");
                        }
                        if ($y >= $height) {
                            // Overflow
                            //echo "ERROR Y overflow on line-wrap, breaking.";
                            break 2;
                        }
                    }
                    $i += $absoluteLen;
                    if ($absoluteLen % 2 != 0) {
                        //echo "Skipped padding byte\n";
                        $i++; // skip a padding byte too
                    }
                }
            } else {
                //echo "Repeat $firstByte instances of $secondByte\n";
                for ($j = 0; $j < $firstByte; $j++) {
                    //echo "Setting byte " . $secondByte . " x=$x, y=$y (j=$j)\n";
                    $outpNum[$y][$x] = $secondByte;
                    $x++;
                    if ($x >= $width) {
                        $x = 0;
                        //$y++;
                        //echo "Wrapping line x=$x, y=$y\n";
                    }
                    if ($y >= $height) {
                        // Overflow
                        //echo "ERROR Y overflow on line-wrap, breaking.";
                        break 2;
                    }
                }
            }
        }
        // Back to string
        $outStringArr = [];
        foreach ($outpNum as $row) {
            $outStringArr[] = pack("C*", ...$row);
        }
        return implode("", $outStringArr); //str_repeat("\0", $rowWidth * $height);
    }

    public function toRasterImage() : RasterImage
    {
        if ($this -> infoHeader -> bpp == 1) {
            $expandedData = PngImage::expandBytes1Bpp($this -> uncompressedData, $this -> infoHeader -> width);
            return IndexedRasterImage::create($this -> infoHeader -> width, $this -> infoHeader -> height, $expandedData, $this -> palette);
        } else if ($this -> infoHeader -> bpp == 2) {
            $expandedData = PngImage::expandBytes2Bpp($this -> uncompressedData, $this -> infoHeader -> width);
            return IndexedRasterImage::create($this->infoHeader -> width, $this -> infoHeader -> height, $expandedData, $this -> palette);
        } else if ($this -> infoHeader -> bpp == 4) {
            $expandedData = PngImage::expandBytes4Bpp($this -> uncompressedData, $this -> infoHeader -> width);
            return IndexedRasterImage::create($this -> infoHeader -> width, $this -> infoHeader -> height, $expandedData, $this -> palette);
        } else if ($this -> infoHeader -> bpp == 8) {
            return IndexedRasterImage::create($this -> infoHeader -> width, $this -> infoHeader -> height, $this -> uncompressedData, $this -> palette);
        } else if ($this -> infoHeader -> bpp == 24) {
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
