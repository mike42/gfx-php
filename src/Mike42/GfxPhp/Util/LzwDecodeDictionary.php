<?php
declare(strict_types=1);

namespace Mike42\GfxPhp\Util;

class LzwDecodeDictionary extends AbstractLzwDictionary
{
    
    protected array $decodeDict;
    
    public function clear(): void
    {
        $count = 2 << ($this -> minCodeSize - 1);
        $this -> decodeDict = range(chr(0), chr($count - 1));
        $this -> clearCode = $count;
        $count++;
        $this -> eodCode = $count;
        $count++;
        $this -> size = $count;
    }
    
    public function get(int $code): string
    {
        if (!$this -> contains($code)) {
            throw new \Exception("LZW decode error; was asked to retrieve code $code but dict is only 0-" . ($this -> size - 1) . ".");
        }
        return $this -> decodeDict[$code];
    }
    
    public function contains(int $code): bool
    {
        return $code < $this -> size;
    }
    
    public function add(string $entry): void
    {
        if ($this -> size == AbstractLzwDictionary::MAX_SIZE) {
            throw new \Exception("LZW code table overflow");
        }
        $this -> decodeDict[$this -> size] = $entry;
        $this -> size++;
    }
}
