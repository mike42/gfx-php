<?php

namespace Mike42\ImagePhp;

interface RasterImage
{
    public function getWidth(): int;
    
    public function getHeight(): int;
    
    public function getRasterData(): string;
    
    public function scale(int $width, int $height): RasterImage;
    
    public function write(string $filename);
    
    public function toRgb() : RgbRasterImage;
    
    public function getPixel(int $x, int $y);
    
    public function setPixel(int $x, int $y, int $value);
    
    public function toGrayscale();
    
    public function toBlackAndWhite();
}
