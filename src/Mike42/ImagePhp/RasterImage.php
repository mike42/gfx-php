<?php

namespace Mike42\ImagePhp;

interface RasterImage
{
    public function getWidth(): int;
    
    public function getHeight(): int;
    
    public function getRasterData(): string;
    
    public function scale(int $width, int $height): RasterImage;
    
    public function write(string $filename);
}
