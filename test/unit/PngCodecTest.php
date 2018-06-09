<?php
namespace test\unit;

use Mike42\GfxPhp\Image;
use PHPUnit\Framework\TestCase;

class PngCodecTest extends TestCase
{
    
    public function testBlackAndWhiteImageLoad() {
        // Simple test of a black-and-white, interlaced image, since
        // we can do a text-based assertion on the actual image content.
        $img = Image::fromFile(__DIR__ . "/../resources/pngsuite/basi0g01.png");
        $result = $img -> toString();
        $expected = "                              ▄█\n" .
                    "                            ▄███\n" .
                    "    ██      ██            ▄█████\n" .
                    "    ██  ▄▄  ██          ▄███████\n" .
                    "    ██  ██  ██        ▄█████████\n" .
                    "     ████████       ▄███████████\n" .
                    "      ██  ██      ▄█████████████\n" .
                    "                ▄███████████████\n" .
                    "              ▄█████████████████\n" .
                    "            ▄███████       █████\n" .
                    "          ▄█████████  ████  ████\n" .
                    "        ▄███████████       █████\n" .
                    "      ▄█████████████  ████  ████\n" .
                    "    ▄███████████████       █████\n" .
                    "  ▄█████████████████████████████\n" .
                    "▄███████████████████████████████\n";
        $this -> assertEquals($expected, $result);
    }
}

