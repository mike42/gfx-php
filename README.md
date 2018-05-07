# gfx-php - The pure PHP graphics library

[![Build Status](https://travis-ci.org/mike42/gfx-php.svg?branch=master)](https://travis-ci.org/mike42/gfx-php) [![Latest Stable Version](https://poser.pugx.org/mike42/gfx-php/v/stable)](https://packagist.org/packages/mike42/gfx-php)
[![Total Downloads](https://poser.pugx.org/mike42/gfx-php/downloads)](https://packagist.org/packages/mike42/gfx-php)
[![License](https://poser.pugx.org/mike42/gfx-php/license)](https://packagist.org/packages/mike42/gfx-php)

This library implements input, output and processing of raster images in pure PHP, so that image
processing extensions (Gd, Imagick) are not required.

This allows developers to eliminate some portability issues from their applications.

## Requirements

- PHP 7.0 or newer

## Examples

- See the `examples/` sub-folder.

## Status & Scope

Currently, we are implementing basic raster operations on select file formats. If you're interested in image processing algorithms, then please consider contributing an implementation.

For algorithms, it appears feasable to implement:

- Color conversions
- Scale
- Crop
- Blur
- Composite
- Mask
- Affine transformations
- Lines, arcs, circles, and rectangles.

And the roadmap for format support:

- The full suite of Netpbm binary and text formats (PNM, PBM, PGM, PPM).
- BMP, which involves RLE (de)compression
- PNG, which involves DEFLATE (de)compression
- GIF and TIFF, which involve LZW (de)compression

In the interests of getting the basic features working first, I'm not currently planning to attempt lossy compression, or formats that are not common on either the web or for printing:

- JPEG
- MNG
- PAM format
- XPM
- More advanced transformations

Also, as we don't have the luxury of pulling in dependencies, I'm considering anything that is not a raster operation out-of-scope:

- All vector image formats (PDF, SVG, EPS, etc).
- Anything involving fonts

### Test data sets

- [imagetestsuite](https://code.google.com/archive/p/imagetestsuite/)
- [bmpsuite](http://entropymine.com/jason/bmpsuite/)
- [pngsuite](http://www.schaik.com/pngsuite/)
- [jburkardt's data sets](https://people.sc.fsu.edu/~jburkardt/data/)

## Similar projects

- [Imagine](https://github.com/avalanche123/Imagine), which wraps available libraries.
