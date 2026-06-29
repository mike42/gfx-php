<?php
declare(strict_types=1);

namespace Mike42\GfxPhp\Codec\Png;

use Mike42\GfxPhp\Codec\Common\DataInputStream;

class PngChunk
{
    private string $type;
    private string $data;
    private int $crc;
    
    public function __construct(string $type, string $data)
    {
        $this -> type = $type;
        $this -> data = $data;
        // Always compute CRC based on the data we have.
        // If this is being read from a chunk's binary, then
        // this will be compared, if not, it will be written.
        $this -> crc = crc32($type . $data);
    }
    
    public function toBin(): string
    {
        $len = strlen($this -> data);
        $lenData = pack("N", $len);
        $bodyData = $this -> type . $this -> data;
        $crcData = pack("N", $this -> crc);
        return $lenData . $bodyData . $crcData;
    }
    
    public function getCrc(): int
    {
        return $this -> crc;
    }
    
    public function getType(): string
    {
        return $this -> type;
    }
    
    public function getData(): string
    {
        return $this -> data;
    }
    
    public static function isValidChunkName(string $name): bool
    {
        if (array_search($name, ["IHDR", "IDAT", "PLTE", "IEND"], true) !== false) {
            // Critical chunks defined as of PNG 1.2
            return true;
        } elseif (preg_match("/[a-z][a-zA-Z]{3}/", $name)) {
            // Ancillary chunks
            return true;
        }
        return false;
    }
    
    public static function fromBin(DataInputStream $in): ?PngChunk
    {
        if ($in -> isEof()) {
            return null;
        }
        $lenData = $in -> read(4);
        $len = unpack("N", $lenData)[1];
        $type = $in -> read(4);
        if (!self::isValidChunkName($type)) {
            // In case this is not a real chunk, we don't want
            // to use random binary data in error messages later.
            throw new \Exception("Bad chunk name");
        }
        $data = $in -> read($len);
        $crcData = $in -> read(4);
        $crc = unpack("N", $crcData)[1];
        $chunk = new PngChunk($type, $data);
        if ($crc != $chunk -> getCrc()) {
            // Refuse to return chunk with bad checksum
            throw new \Exception("CRC did not match on $type chunk");
        }
        return $chunk;
    }
    
    public function toString(): string
    {
        return $this -> type . " chunk";
    }
}
