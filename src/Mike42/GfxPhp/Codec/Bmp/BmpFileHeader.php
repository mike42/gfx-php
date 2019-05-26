<?php


namespace Mike42\GfxPhp\Codec\Bmp;


use Mike42\GfxPhp\Codec\Common\DataInputStream;

class BmpFileHeader
{
    public $offset;
    public $size;

    public function __construct(int $size, int $offset) {
        $this -> size = $size;
        $this -> offset = $offset;
    }

    public static function fromBinary(DataInputStream $data) : BmpFileHeader
    {
        $fileHeaderData = $data->read(12);
        $fields = unpack("Vsize/vreserved1/vreserved2/Voffset", $fileHeaderData);
        return new BmpFileHeader($fields['size'], $fields['offset']);
    }
}