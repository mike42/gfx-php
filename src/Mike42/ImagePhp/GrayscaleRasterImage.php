<?php
namespace Mike42\ImagePhp;

use Mike42\ImagePhp\Codec\PnmCodec;

class GrayscaleRasterImage extends AbstractRasterImage
{
    protected $width;

    protected $height;

    protected $data;

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
        $byte = $y * $this -> width + $x;
        $this -> data[$byte] = $value & 0xFF;
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

    protected function __construct($width, $height, array $data)
    {
        $this -> width = $width;
        $this -> height = $height;
        $this -> data = $data;
    }

    public static function create($width, $height, array $data = null) : BlackAndWhiteRasterImage
    {
        $expectedBytes = $width * $height;
        if ($data === null) {
            $data = array_values(array_fill(0, $expectedBytes, 255));
        }
        return new BlackAndWhiteRasterImage($width, $height, $data);
    }

    public function getRasterData(): string
    {
        return pack("C*", ... $this -> data);
    }
}
