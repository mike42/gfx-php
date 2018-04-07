<?php

namespace Mike42\ImagePhp;

interface RasterImage
{
    public function getWidth(): int;
    
    public function getHeight(): int;
    
    public function getRasterData(): string;
}
