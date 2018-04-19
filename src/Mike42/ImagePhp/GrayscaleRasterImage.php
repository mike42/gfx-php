<?php
namespace Mike42\ImagePhp;

use Mike42\ImagePhp\Codec\PnmCodec;

class GrayscaleRasterImage extends AbstractRasterImage
{
    protected $width;

    protected $height;

    protected $data;
    
    protected $maxVal;

    public function getWidth() : int
    {
        return $this -> width;
    }

    public function getHeight() : int
    {
        return $this -> height;
    }

    public function setPixel(int $x, int $y, $value)
    {
        if ($x < 0 || $x >= $this -> width) {
            return;
        }
        if ($y < 0 || $y >= $this -> height) {
            return;
        }
        // Cut off at max and min
        if ($value < 0) {
            $value = 0;
        } else if ($value > $this -> maxVal) {
            $value = $this -> maxVal;
        }
        $byte = $y * $this -> width + $x;
        $this -> data[$byte] = $value;
    }

    public function getPixel(int $x, int $y)
    {
        if ($x < 0 || $x >= $this -> width) {
            return 0;
        }
        if ($y < 0 || $y >= $this -> height) {
            return 0;
        }
        $byte = $y * $this -> width + $x;
        return $this -> data[$byte];
    }

    protected function __construct($width, $height, array $data, int $maxVal)
    {
        $this -> width = $width;
        $this -> height = $height;
        $this -> data = $data;
        $this -> maxVal = $maxVal;
    }
    
    public function getMaxVal()
    {
        return $this -> maxVal;
    }

    public static function create($width, $height, array $data = null, $maxVal = 255) : GrayscaleRasterImage
    {
        $expectedBytes = $width * $height;
        if ($data === null) {
            $data = array_values(array_fill(0, $expectedBytes, 255));
        }
        return new GrayscaleRasterImage($width, $height, $data, $maxVal);
    }

    public function getRasterData(): string
    {
        if ($this -> maxVal > 255) {
            return pack("n*", ... $this -> data);
        }
        return pack("C*", ... $this -> data);
    }
    
    public function mapColor(int $srcColor, RasterImage $destImage)
    {
        if ($destImage instanceof GrayscaleRasterImage) {
            if ($destImage -> maxVal == $this -> maxVal) {
                return $srcColor;
            }
            $destVal =  intdiv($srcColor * $destImage -> maxVal, $this -> maxVal);
            //echo "$srcColor / " . $this -> maxVal . " -> $destVal / " . $destImage -> maxVal . "\n";
            return $destVal;
        }
        throw new Exception("Cannot map colors");
    }
}
