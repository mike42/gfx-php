<?php
use Mike42\ImagePhp\BlackAndWhiteRasterImage;

use PHPUnit\Framework\TestCase;

class PbmImageTest extends TestCase
{

    public function testCreate()
    {
        $foo = BlackAndWhiteRasterImage::create(1, 1);
    }
}