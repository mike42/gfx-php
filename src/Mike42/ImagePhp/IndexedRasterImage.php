<?php
namespace Mike42\ImagePhp;

class IndexedRasterImage extends AbstractRasterImage
{
    protected $width;
    
    protected $height;
    
    protected $data;
    
    protected $maxVal;
    
    protected $palette;
    
    protected $transparentColor = null;
    
    protected function __construct($width, $height, array $data, int $maxVal, array $palette)
    {
        $this -> width = $width;
        $this -> height = $height;
        $this -> data = $data;
        $this -> palette = $palette;
    }

    public function getPalette()
    {
        return $this -> palette;
    }
    
    public function getRasterData(): string
    {

        if ($this -> maxVal > 255) {
            return pack("n*", ... $this -> data);
        }
            return pack("C*", ... $this -> data);
    }

    public function getHeight(): int
    {
        return $this -> height;
    }

    public function getMaxVal()
    {
        return $this -> maxVal;
    }
    
    public function setPixel(int $x, int $y, int $value)
    {
        if ($x < 0 || $x >= $this -> width) {
            return;
        }
        if ($y < 0 || $y >= $this -> height) {
            return;
        }
            // Use 0 if $value is out of range
        if ($value < 0 || $value > $this -> maxVal) {
            $value = 0;
        }
            $byte = $y * $this -> width + $x;
            $this -> data[$byte] = $value;
    }

    public function toRgb(): RgbRasterImage
    {
        $img = RgbRasterImage::create($this -> width, $this -> height);
        for ($y = 0; $y < $this -> height; $y++) {
            for ($x = 0; $x < $this -> width; $x++) {
                $original = $this -> indexToRgb($this -> getPixel($x, $y));
                $val = $img -> rgbToInt($original[0], $original[1], $original[2]);
                $img -> setPixel($x, $y, $val);
            }
        }
        return $img;
    }

    public function toBlackAndWhite() : BlackAndWhiteRasterImage
    {
        $img = BlackAndWhiteRasterImage::create($this -> width, $this -> height);
        for ($y = 0; $y < $this -> height; $y++) {
            for ($x = 0; $x < $this -> width; $x++) {
                $original = $this -> indexToRgb($this -> getPixel($x, $y));
                $lightness = intdiv($original[0] + $original[1] + $original[2], 3);
                $img -> setPixel($x, $y, $lightness > 128 ? 0 : 1);
            }
        }
        return $img;
    }

    public function toGrayscale(): GrayscaleRasterImage
    {
        $img = GrayscaleRasterImage::create($this -> width, $this -> height);
        for ($y = 0; $y < $this -> height; $y++) {
            for ($x = 0; $x < $this -> width; $x++) {
                $original = $this -> indexToRgb($this -> getPixel($x, $y));
                $lightness = intdiv($original[0] + $original[1] + $original[2], 3);
                $img -> setPixel($x, $y, $lightness);
            }
        }
        return $img;
    }

    public function getPixel(int $x, int $y)
    {
        if ($x < 0 || $x >= $this -> width) {
            return;
        }
        if ($y < 0 || $y >= $this -> height) {
            return;
        }
        $byte = $y * $this -> width + $x;
        return $this -> data[$byte];
    }

    public function getWidth(): int
    {
        return $this -> width;
    }

    public function toIndexed(): IndexedRasterImage
    {
        return clone $this;
    }

    public function indexToRgb(int $index)
    {
        if ($index >= 0 && $index < count($this -> palette)) {
            // Defined index
            return $this -> palette[$index];
        }
        // Black
        return [0, 0, 0];
    }

    public function rgbToIndex(array $rgb)
    {
        $ret = array_search($rgb, $this -> palette, true);
        if ($ret !== false) {
            // Index of defined color
            return $ret;
        }
        // First color.
        return 0;
    }

    public function getTransparentColor()
    {
        return $this -> transparentColor;
    }

    public function setTransparentColor(int $color = null)
    {
        $this -> transparentColor = $color;
    }
    
    public static function create(int $width, int $height, array $data = null, array $palette = null, int $maxVal = 255)
    {
        $expectedSize = $width * $height;
        if ($data == null) {
            // Empty image, white background
            if (count($palette) == 0) {
                $palette = [[255, 255, 255]]; // White
            }
            $data = array_fill(0, $expectedSize, 0);
        }
        // Validation
        $actualSize = count($data);
        if ($actualSize !== $expectedSize) {
            throw new \Exception("Expected $expectedSize pixels for $width x $height image, but got $actualSize.");
        }
        // So that we know that we aren't missing any keys, palette should be array, not map.
        // Palette entries must be array of three values up to 255 for R, G, B.
        // It's slightly easier to just convert the structure than to check all of this
        $newPalette = [];
        foreach ($palette as $color) {
            if (!is_array($color) || count($color) !== 3) {
                throw new \Exception("Bad palette data: Need three values per entry.");
            }
            // Eradicate keys and non-numeric values
            $color = array_values($color);
            $color = [(int)$color[0], (int)$color[1], (int)$color[2]];
            // Gets written to image formats with 8-bits for each value
            if (max($color) > 255) {
                throw new \Exception("Bad palette data: Entries cannot exceed 255.");
            }
            $newPalette[] = $color;
        }
        // Validate that we can render this data with this palette
        $highestPaletteValue = count($palette) - 1;
        $highestPixel = max($data);
        if ($highestPixel > $highestPaletteValue) {
            throw new \Exception("Expected all image values to be <= the palette size ($highestPaletteValue), but the highest is $highestPixel.");
        }
        $highestPixel = max($data);
        if ($highestPixel > $highestPaletteValue) {
            throw new \Exception("Image data cannot be rendered with this palette. The palette contains values up to $highestPaletteValue, but image values go up to $highestPixel.");
        }
        return new IndexedRasterImage($width, $height, $data, $maxVal, $palette);
    }
}