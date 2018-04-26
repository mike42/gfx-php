<?php
namespace Mike42\ImagePhp;

class Palette
{
    protected $capacity;
    protected $content;

    public function __construct(int $capacity, $content = [])
    {
        // Validate palette construction
        if (count($content) > $capacity) {
            throw new Exception("Palette contains too many colors");
        }
        // TODO check that $content[] is sequential and numeric
        $this -> capacity = $capacity;
        $this -> content = $content;
    }

    public function getSize()
    {
        return count($this > content);
    }

    public function add(int $val)
    {
        $key = $this -> content[$val];
        $this -> content[] = $val;
        return count($this -> content) - 1;
    }

    public function values()
    {
        return $this -> content();
    }
}
