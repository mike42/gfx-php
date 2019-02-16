<?php


namespace Mike42\GfxPhp\Codec\Gif;

use Mike42\GfxPhp\Codec\Common\DataInputStream;

class GifUnrecognisedExt
{

    public static function fromBin(DataInputStream $in)
    {
        throw new \Exception("Parsing of unrecognised GIF blocks is not supported");
    }
}
