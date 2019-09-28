<?php

namespace Mike42\GfxPhp\Codec\Bmp;

class BmpBitfieldDecoder
{
    private $bitfields;

    public function __construct(BmpColorBitfield $bitfields)
    {
        $this -> bitfields = $bitfields;
    }

    public function read16bit(array $inpBytes) : array
    {
        // TODO use mask getNormalisedValue.
        $red = $this -> bitfields -> getRed();
        $green = $this -> bitfields -> getGreen();
        $blue = $this -> bitfields -> getBlue();

        $redBits = $red -> getLen();
        $blueBits = $blue -> getLen();
        $greenBits = $green -> getLen();

        // Fill output array to 1.5 times the size of the input array
        $pixelCount = intdiv(count($inpBytes), 2);
        $outpBytes = array_fill(0, $pixelCount * 3, 0);

        // How many bits left to shift to get the extracted value to take up 8 bits
        $blueWriteShift = 8 - $blueBits;
        $greenWriteShift = 8 - $greenBits;
        $redWriteShift = 8 - $redBits;
        // Number of bits right to shift to get the extracted value to top up otherwise unset least-significant bits.
        // This allows us to map values to the full 0-255 range, as long as at least 4 bits are used.
        $blueWriteLsbShift = $blueBits - $blueWriteShift;
        $greenWriteLsbShift = $greenBits - $greenWriteShift;
        $redWriteLsbShift = $redBits - $redWriteShift;
        for ($i = 0; $i < $pixelCount; $i++) {
            // Extract little-endian color code in 16 bit space
            $inpColor = ($inpBytes[$i * 2 + 1] << 8) + ($inpBytes[$i * 2]);
            // Get 5-bit red, blue and green components
            $blueLevel = ($inpColor & $blue -> getMask()) >> $blue -> getOffset();
            $greenLevel = ($inpColor & $green -> getMask()) >> $green -> getOffset();
            $redLevel = ($inpColor & $red -> getMask()) >> $red -> getOffset();
            // Store as 8-bit components
            $outpBytes[$i * 3] = ($redLevel << $redWriteShift) + ($redLevel >> $redWriteLsbShift);
            $outpBytes[$i * 3 + 1] = ($greenLevel << $greenWriteShift) + ($greenLevel >> $greenWriteLsbShift);
            $outpBytes[$i * 3 + 2] = ($blueLevel << $blueWriteShift) + ($blueLevel >> $blueWriteLsbShift);
        }
        return $outpBytes;
    }
}
