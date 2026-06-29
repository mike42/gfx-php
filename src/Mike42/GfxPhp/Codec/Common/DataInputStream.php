<?php
declare(strict_types=1);

namespace Mike42\GfxPhp\Codec\Common;

interface DataInputStream
{
    public function read(int $bytes);
    public function isEof();
    public function peek(int $bytes);
    public function advance(int $bytes);
}
