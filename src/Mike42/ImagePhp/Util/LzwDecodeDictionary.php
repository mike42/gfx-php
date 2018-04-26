<?php
namespace Mike42\ImagePhp\Util;

class LzwDecodeDictionary extends AbstractLzwDictionary
{
    
    protected $decodeDict;
    
    public function clear()
    {
        $count = 2 << ($this -> minCodeSize - 1);
        $this -> decodeDict = range(chr(0), chr($count - 1));
        //$this -> encodeDict = array_flip($this -> decodeDict);
        $this -> clearCode = $count;
        $count++;
        $this -> eodCode = $count;
        $count++;
        $this -> size = $count;
        
        //print_r($this -> decodeDict);
        //print_r($this -> encodeDict);
    }
    
    public function get(int $code)
    {
        if (!$this -> contains($code)) {
            throw new \Exception("LZW decode error; was asked to retrieve code $code but dict is only 0-" . ($this -> size - 1) . ".");
        }
        return $this -> decodeDict[$code];
    }
    
    public function contains(int $code)
    {
        return $code < $this -> size;
    }
    
    public function add(string $entry)
    {
        if ($this -> size == AbstractLzwDictionary::MAX_SIZE) {
            throw new \Exception("LZW code table overflow");
        }
        //$hexVal = bin2hex($entry);
        //$l = strlen($entry);
        //echo "LzwDecodeDictionary ADDING 0x$hexVal (len=$l) to dict @ position " . $this -> size ."\n";
        $this -> decodeDict[$this -> size] = $entry;
        $this -> size++;
    }
}
