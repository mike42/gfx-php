<?php


namespace Mike42\GfxPhp\Codec\Gif;

use Mike42\GfxPhp\Codec\Common\DataInputStream;

class GifPlaintextExt
{

    public static function fromBin(DataInputStream $in) : GifPlaintextExt
    {
        throw new \Exception("GIF_EXTENSION_PLAINTEXT not implemented");
    }
}
