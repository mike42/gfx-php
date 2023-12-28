<?php
declare(strict_types=1);

namespace Mike42\GfxPhp\Codec\Common;

class DataBlobInputStream implements DataInputStream
{
    private string $data;
    private int $offset;

    public function __construct(string $data)
    {
        $this -> data = $data;
        $this -> offset = 0;
    }

    public function read(int $bytes): string
    {
        $chunk = $this -> peek($bytes);
        $this -> advance($bytes);
        return $chunk;
    }

    public function advance(int $bytes): void
    {
        $this -> offset += $bytes;
    }

    public function peek(int $bytes): string
    {
        $chunk = substr($this -> data, $this -> offset, $bytes);
        if ($chunk === false) {
            throw new \Exception("End of file reached, cannot retrieve more data.");
        }
        $read = strlen($chunk);
        if ($read !== $bytes) {
            throw new \Exception("Unexpected end of file, needed $bytes but read $read");
        }
        return $chunk;
    }

    public function isEof(): bool
    {
        return $this -> offset >= strlen($this -> data);
    }
 
    public static function fromBlob(string $blob): DataBlobInputStream
    {
        return new DataBlobInputStream($blob);
    }
    
    public static function fromFilename(string $filename): DataBlobInputStream
    {
        $blob = file_get_contents($filename);
        if ($blob === false) {
            throw new \Exception($filename);
        }
        return self::fromBlob($blob);
    }
}
