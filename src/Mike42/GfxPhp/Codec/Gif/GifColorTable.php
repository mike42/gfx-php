<?php
declare(strict_types=1);

namespace Mike42\GfxPhp\Codec\Gif;

use Mike42\GfxPhp\Codec\Common\DataInputStream;

class GifColorTable
{
    private array $palette;

    public function __construct(array $palette)
    {
        $this -> palette = $palette;
    }

    public static function fromBin(DataInputStream $in, int $globalColorTableSize): GifColorTable
    {
        $tableData = $in -> read($globalColorTableSize * 3);
        $paletteArr = array_values(unpack("C*", $tableData));
        $palette = array_chunk($paletteArr, 3);
        return new GifColorTable($palette);
    }

    public function getPalette(): array
    {
        return $this->palette;
    }
}
