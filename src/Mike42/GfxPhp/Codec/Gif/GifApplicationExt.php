<?php


namespace Mike42\GfxPhp\Codec\Gif;

use Mike42\GfxPhp\Codec\Common\DataInputStream;

class GifApplicationExt
{

    public static function fromBin(DataInputStream $in)
    {
        throw new \Exception("GIF_EXTENSION_APPLICATION not implemented");
    }
}
