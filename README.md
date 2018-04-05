# image-php - The pure PHP image processing library

[![Build Status](https://travis-ci.org/mike42/image-php.svg?branch=master)](https://travis-ci.org/mike42/image-php)

This project implements raster graphics processing in pure PHP.

Currently, it can perform basic operations on the netpbm portable bitmap (P4) format.

## Example Usage

I initially implemented this to generate placeholder glyphs
for the Thermal Sans Mono font, which involves rendering
characters from a small bitmap font into a rectangle:

```php
use Mike42\ImagePhp\PbmImage;

// Inputs
$outFile = "out.pbm";
$font = PbmImage::fromFile(dirname(__FILE__). "/fonts/5x7hex.pbm");
$codePoint = str_split("0A2F");
$charWidth = 5;
$charHeight = 7;

// Create small image for each character
$chars = ["0", "1", "2", "3", "4", "5", "6", "7", "8", "9", "A", "B", "C", "D", "E", "F"];
$subImages = [];
for($i = 0; $i < count($codePoint); $i++) {
  $id = array_search($codePoint[$i], $chars);
  if($id === false) {
    die("Don't know how to encode " . $codePoint[$i]);
  }
  $subImages[] = $font -> subImage($id * $charWidth, 0, $charWidth, $charHeight);
}

// Place four images in a box
$out = PbmImage::create(18, 17);
$out -> rect(0, 0, 18, 17);
$out -> rect(3, 0, 12, 17, true, 0, 0);
$out -> compose($subImages[0], 0, 0, 4, 1, $charWidth, $charHeight);
$out -> compose($subImages[1], 0, 0, 10, 1, $charWidth, $charHeight);
$out -> compose($subImages[2], 0, 0, 4, 9, $charWidth, $charHeight);
$out -> compose($subImages[3], 0, 0, 10, 9, $charWidth, $charHeight);

# Print output for debugging ;)
echo $out -> toString();
$out -> write($outFile);
```

This outputs a small PBM image, and this text:

```
█▀▀  ▄▄    ▄▄  ▀▀█
█   █  █  █  █   █
█   █  █  █▀▀█   █
█   ▀▄▄▀  █  █   █
█   ▄▄▄   ▄▄▄▄   █
█      █  █      █
█   █▀▀▀  █▀▀    █
█   █▄▄▄  █      █
▀▀▀            ▀▀▀
````

## Roadmap

It's hoped that this can deliver an independently useful library
for basic raster processing, but it's early days.

If you're interested in image processing algorithms, then please consider contributing an implementation.

For algorithms, we might do:

- Color conversions, scale, crop, blur, composite, mask, affine transformations, lines, arcs, circles, and rectangles.

And the roadmap for format support:

- We should be able to support the full suite of netpbm and BMP formats in pure PHP, while PNG can be implemented with the help of `zlib`.
- TIFF/GIF will require a more detailed implementation.
- JPEG support is not expected to be feasable without using an external library, if you're looking for JPEG support you should use Imagick or Gd directly.
