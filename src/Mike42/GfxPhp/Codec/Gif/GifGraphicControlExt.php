<?php


namespace Mike42\GfxPhp\Codec\Gif;

use Mike42\GfxPhp\Codec\Common\DataInputStream;

class GifGraphicControlExt
{
    public function __construct()
    {
    }

    public static function fromBin(DataInputStream $in) : GifGraphicControlExt
    {
        // TODO
        $in -> read(8);
        return new GifGraphicControlExt();
    }
}
