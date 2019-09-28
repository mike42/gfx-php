<?php

namespace Mike42\GfxPhp\Codec\Bmp;

use PHPUnit\Framework\TestCase;

class BmpColorMaskTest  extends TestCase
{
    public function testEmpty() {
        $mask = new BmpColorMask(0x00); // 00000000
        $this -> assertEquals(0, $mask -> getLen());
        $this -> assertEquals(0, $mask -> getOffset());
    }

    public function testOne() {
        $mask = new BmpColorMask(0x01);// 00000001
        $this -> assertEquals(1, $mask -> getLen());
        $this -> assertEquals(0, $mask -> getOffset());
    }

    public function testOffset() {
        $mask = new BmpColorMask(0x10);// 00010000
        $this -> assertEquals(1, $mask -> getLen());
        $this -> assertEquals(4, $mask -> getOffset());
    }

    public function testLength() {
        $mask = new BmpColorMask(0x30); // 00110000
        $this -> assertEquals(2, $mask -> getLen());
        $this -> assertEquals(4, $mask -> getOffset());
    }

    public function testNonContiguous() {
        $this -> expectException(\Exception::class);
        $mask = new BmpColorMask(0x50); // 01010000
        $this -> assertEquals(3, $mask -> getLen());
        $this -> assertEquals(4, $mask -> getOffset());
    }
}
