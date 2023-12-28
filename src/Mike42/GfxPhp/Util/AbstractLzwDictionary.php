<?php
declare(strict_types=1);

namespace Mike42\GfxPhp\Util;

abstract class AbstractLzwDictionary
{
    const MAX_SIZE = 4096;
    
    protected int $minCodeSize;
    protected int $clearCode;
    protected int $eodCode;
    protected int $size;
    
    /**
     * @return number
     */
    public function getClearCode(): int
    {
        return $this->clearCode;
    }
    
    /**
     * @return number
     */
    public function getEodCode(): int
    {
        return $this->eodCode;
    }
    
    public function __construct(int $minCodeSize)
    {
        $this -> minCodeSize = $minCodeSize;
        $this -> clear();
    }
    
    public function getSize(): int
    {
        return $this -> size;
    }
    
    abstract public function clear();
    
    abstract public function add(string $entry);
}
