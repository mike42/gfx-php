<?php


namespace Mike42\GfxPhp\Codec\Gif;

use Mike42\GfxPhp\Codec\Common\DataInputStream;

class GifCommentExt
{

    public static function fromBin(DataInputStream $in)
    {
        throw new \Exception("GIF_EXTENSION_COMMENT not implemented");
    }
}
