<?php

namespace Mike42\ImagePhp;

use Mike42\ImagePhp\Codec\PnmCodec;

abstract class AbstractRasterImage implements RasterImage
{
    public function rect($startX, $startY, $width, $height, $filled = false, $outline = 1, $fill = 1)
    {
        $this -> horizontalLine($startY, $startX, $startX + $width - 1, $outline);
        $this -> horizontalLine($startY + $height - 1, $startX, $startX + $width - 1, $outline);
        $this -> verticalLine($startX, $startY, $startY + $height - 1, $outline);
        $this -> verticalLine($startX + $width - 1, $startY, $startY + $height - 1, $outline);
        if ($filled) {
            // Fill center of the rectangle
            for ($y = $startY + 1; $y < $startY + $height - 1; $y++) {
                for ($x = $startX + 1; $x < $startX + $width - 1; $x++) {
                    $this -> setPixel($x, $y, $fill);
                }
            }
        }
    }
 
    protected function horizontalLine($y, $startX, $endX, $outline)
    {
        for ($x = $startX; $x <= $endX; $x++) {
            $this -> setPixel($x, $y, $outline);
        }
    }
    
    protected function verticalLine($x, $startY, $endY, $outline)
    {
        for ($y = $startY; $y <= $endY; $y++) {
            $this -> setPixel($x, $y, $outline);
        }
    }
    
    public function write(string $filename)
    {
        // Temporary function while we don't support many formats...
        $blob = PnmCodec::getInstance() -> encode($this);
        file_put_contents($filename, $blob);
    }
    
    public function scale(int $width, int $height) : RasterImage
    {
        $img = $this::create($width, $height);
      
        return $img;
    }
}
