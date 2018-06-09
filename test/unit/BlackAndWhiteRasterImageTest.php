<?php
use Mike42\GfxPhp\BlackAndWhiteRasterImage;

use PHPUnit\Framework\TestCase;

class BlackAndWhiteRasterImageTest extends TestCase
{

    public function testCreate()
    {
        $foo = BlackAndWhiteRasterImage::create(1, 1);
    }
}