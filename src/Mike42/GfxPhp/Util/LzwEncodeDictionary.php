<?php

declare(strict_types=1);

namespace Mike42\GfxPhp\Util;

class LzwEncodeDictionary extends AbstractLzwDictionary
{
    protected array $encodeDict;

    public function clear(): void
    {
        $count = 2 << ($this -> minCodeSize - 1);
        // Max 256 entries pre-loaded into table (one per possible byte value)
        $this -> encodeDict = array_flip(range(chr(0), chr(min($count - 1, 255))));
        $this -> clearCode = $count;
        $count++;
        $this -> eodCode = $count;
        $count++;
        $this -> size = $count;
    }

    public function get(string $code): int
    {
        if (!$this -> contains($code)) {
            throw new \Exception("LZW encode error; code sequence not in dictionary.");
        }
        return $this -> encodeDict[$code];
    }

    public function contains(string $code): bool
    {
        return isset($this -> encodeDict[$code]);
    }

    public function add(string $entry): void
    {
        if ($this -> size == AbstractLzwDictionary::MAX_SIZE) {
            throw new \Exception("LZW code table overflow");
        }
        $this -> encodeDict[$entry] = $this -> size;
        $this -> size++;
    }
}
