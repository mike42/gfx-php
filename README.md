# gfx-php - The pure PHP graphics library

[![Build Status](https://travis-ci.org/mike42/gfx-php.svg?branch=master)](https://travis-ci.org/mike42/gfx-php) [![Latest Stable Version](https://poser.pugx.org/mike42/gfx-php/v/stable)](https://packagist.org/packages/mike42/gfx-php)
[![Total Downloads](https://poser.pugx.org/mike42/gfx-php/downloads)](https://packagist.org/packages/mike42/gfx-php)
[![License](https://poser.pugx.org/mike42/gfx-php/license)](https://packagist.org/packages/mike42/gfx-php) [![Coverage Status](https://coveralls.io/repos/github/mike42/gfx-php/badge.svg?branch=master)](https://coveralls.io/github/mike42/gfx-php?branch=master)

This library implements input, output and processing of raster images in pure PHP, so that image
processing extensions (Gd, Imagick) are not required.

This allows developers to eliminate some portability issues from their applications.

## Requirements

- PHP 7.0 or newer.
- zlib extension, for reading PNG files.

## Get started

- Have a read of the documentation at [gfx-php.readthedocs.io](https://gfx-php.readthedocs.io/)
- See the `examples/` sub-folder for snippets.

## Status & scope

Currently, we are implementing basic raster operations on select file formats.

See related documentation for:

- [Available input file formats](https://gfx-php.readthedocs.io/en/latest/user/formats.html#input-formats).
- [Available output file formats](https://gfx-php.readthedocs.io/en/latest/user/formats.html#output-formats).
- [Available image operations](https://gfx-php.readthedocs.io/en/latest/user/operations.html).

If you're interested in image processing algorithms, then please consider contributing an implementation.

For algorithms, it appears feasable to implement:

- Rotate
- Layered operations
- Affine transformations
- Lines, arcs, circles, and rectangles.

And sill on the roadmap for format support:

- BMP input, which involves RLE decompression (BMP output is already available).
- GIF input, which involves LZW decompression (GIF output is already available).
- TIFF input and output, which also involves LZW (de)compression.

In the interests of getting the basic features working first, there is no current plan to attempt lossy compression, or formats that are not common on either the web or for printing, eg:

- JPEG
- MNG
- PAM format
- XPM
- .. etc.

Also, as we don't have the luxury of pulling in dependencies, I'm considering anything that is not a raster operation out-of-scope:

- All vector image formats (PDF, SVG, EPS, etc).
- Anything involving vector fonts

### Test data sets

- [imagetestsuite](https://code.google.com/archive/p/imagetestsuite/)
- [bmpsuite](http://entropymine.com/jason/bmpsuite/)
- [pngsuite](http://www.schaik.com/pngsuite/)
- [jburkardt's data sets](https://people.sc.fsu.edu/~jburkardt/data/)

## Similar projects

- [Imagine](https://github.com/avalanche123/Imagine), which wraps available libraries.
