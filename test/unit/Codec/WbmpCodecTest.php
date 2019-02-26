<?php

use Mike42\GfxPhp\BlackAndWhiteRasterImage;
use Mike42\GfxPhp\Codec\WbmpCodec;
use PHPUnit\Framework\TestCase;

class WbmpCodecTest extends TestCase {
    const WBMP_IMAGE = "\x00\x00\x0c\x06\x24\x90\xff\xf0\x49\x20\xff\xf0\x92\x40\xff\xf0";

    public function testDecode() {
        $codec = new WbmpCodec();
        $image = $codec -> decode(self::WBMP_IMAGE) -> toBlackAndWhite();
        $this -> assertEquals(12, $image -> getWidth());
        $this -> assertEquals(6, $image -> getHeight());
        $content =  "▀▀ ▀▀ ▀▀ ▀▀ \n" .
                    "▀ ▀▀ ▀▀ ▀▀ ▀\n" .
                    " ▀▀ ▀▀ ▀▀ ▀▀\n";
        $this -> assertEquals($content, $image -> toString());
    }

    public function testEncode() {
        // Raster representation is inverse to WBMP format.
        $image = BlackAndWhiteRasterImage::create(12, 6, [0xdb, 0x6f, 0x00, 0x0f, 0xb6, 0xdf, 0x00, 0x0f, 0x6d, 0xbf, 0x00, 0x0f]);
        $codec = new WbmpCodec();
        $data = $codec -> encode($image, "wbmp");
        $this -> assertEquals(self::WBMP_IMAGE, $data);
    }
}