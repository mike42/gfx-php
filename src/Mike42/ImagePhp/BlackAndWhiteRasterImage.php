<?php
namespace Mike42\ImagePhp;

use Mike42\ImagePhp\Codec\PnmCodec;

/**
 * Small implementation of basic raster operations on PBM files to support
 * creation of placeholder glyphs
 */
class BlackAndWhiteRasterImage extends AbstractRasterImage
{
    protected $width;

    protected $bytesPerRow;

    protected $height;

    protected $data;

    public function invert()
    {
        array_walk($this -> data, 'self::invertByte');
    }

    public function clear()
    {
        array_walk($this -> data, 'self::clearByte');
    }

    protected static function invertByte(int &$item, $key)
    {
        $item = ~ $item;
    }

    protected static function clearByte(int &$item, $key)
    {
        $item = 0;
    }

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
        $byte = $y * $this -> bytesPerRow + intdiv($x, 8);
        $bit = $x % 8;
        if ($value === 0) {
          // Clear
            $this -> data[$byte] &= ~(1 << (7 - $bit));
        } else {
          // Set
            $this -> data[$byte] |= (1 << (7 - $bit));
        }
    }

    public function getPixel(int $x, int $y)
    {
        if ($x < 0 || $x >= $this -> width) {
            return 0;
        }
        if ($y < 0 || $y >= $this -> height) {
            return 0;
        }
        $byte = $y * $this -> bytesPerRow + intdiv($x, 8);
        $bit = $x % 8;
        return ($this -> data[$byte] >> (7 - $bit)) & 0x01;
    }

    protected function __construct($width, $height, array $data)
    {
        $this -> width = $width;
        $this -> height = $height;
        $this -> data = $data;
        $this -> bytesPerRow = intdiv($width + 7, 8);
    }

    public function write($filename)
    {
        // Temporary function while we don't support many formats...
        $blob = PnmCodec::getInstance() -> encode($this);
        file_put_contents($filename, $blob);
    }

    public static function create($width, $height, array $data = null) : BlackAndWhiteRasterImage
    {
        $bytesPerRow = intdiv($width + 7, 8);
        $expectedBytes = $bytesPerRow * $height;
        if ($data === null) {
            $data = array_values(array_fill(0, $expectedBytes, 0));
        }
        return new BlackAndWhiteRasterImage($width, $height, $data);
    }

    public function toString()
    {
        $out = "";
        for ($y = 0; $y < $this -> getHeight(); $y += 2) {
            for ($x = 0; $x < $this -> getWidth(); $x++) {
                $upper = $this -> getPixel($x, $y) == 1;
                $lower = $this -> getPixel($x, $y + 1) == 1;
                if ($upper && $lower) {
                    $char = "█";
                } else if ($upper) {
                    $char = "▀";
                } else if ($lower) {
                    $char = "▄";
                } else {
                    $char = " ";
                }
                $out .= $char;
            }
            $out .= "\n";
        }
        return $out;
    }

    public function subImage(int $startX, int $startY, int $width, int $height)
    {
        $ret = $this::create($width, $height);
        $ret -> compose($this, $startX, $startY, 0, 0, $width, $height);
        return $ret;
    }

    public function compose(RasterImage $source, int $startX, int $startY, int $destStartX, int $destStartY, int $width, int $height)
    {
        for ($y = 0; $y < $height; $y++) {
            $srcY = $y + $startY;
            $destY = $y + $destStartY;
            for ($x = 0; $x < $width; $x++) {
                $srcX = $x + $startX;
                $destX = $x + $destStartX;
                $this -> setPixel($destX, $destY, $source -> getPixel($srcX, $srcY));
            }
        }
    }
    public function getRasterData(): string
    {
        return pack("C*", ... $this -> data);
    }
}
