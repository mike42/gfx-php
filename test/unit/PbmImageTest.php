<?php
use Mike42\ImagePhp\PbmImage;

use PHPUnit\Framework\TestCase;

class PbmImageTest extends TestCase
{

    public function testCreate()
    {
        $foo = PbmImage::create(1, 1);
    }
}