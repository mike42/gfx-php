File formats
============

.. contents::
   :local:

Input formats
-------------

Files are read from a file or URL by using the :meth:`Image::fromFile()` function:

.. code-block:: php
  
  use Mike42\ImagePhp\Image;
  $tux = Image::fromFile("tux.pbm")

If the your image is not being read from a file, then :meth:`Image::fromBlob()` can load it instead:

.. code-block:: php

  use Mike42\ImagePhp\Image;
  $tuxStr = "...";
  $tux = Image::fromBlob($tuxStr, "tux.pbm");

In either case, the input format is determined using the file's `magic number`_.

.. _magic number: https://en.wikipedia.org/wiki/Magic_number_(programming)

Netpbm Formats
^^^^^^^^^^^^^^

The Netpbm formats are a series of uncompressed bitmap formats, which can represent most types of image. The formats can be read by ``image-php``.

:PNM: This is a file extension only. Files carrying ``.pnm`` extension can carry any of the below formats.
:PPM: This is a color raster format. A PPM file is identified by the P6 magic number, and will be loaded into an instance of :class:`RgbRasterImage`.
:PGM: This is a monochrome raster format. A PGM file is identified by the P5 magic number, and will be loaded instance of :class:`GrayscaleRasterImage`.
:PBM: This is a 1-bit bitmap format. A PBM file is identified by the P4 header, and loaded into an instance of :class:`BlackAndWhiteRasterImage`.

Each of these formats has both a binary and text encoding. ``image-php`` only supports the binary encodings at this stage.

Output formats
--------------

When you write a :class:`RasterImage` to a file, you need to specify a filename. The extension on this file is used to determine the desired output format.

There is currently no mechanism to write a file directly to a string.

PNG
^^^

The PNG format is selected by using the ``png`` file extension when you call :func:`RasterImage::write()`.

.. code-block:: php

  $tux -> write("tux.png");

This library will currently output PNG files as RGB data. If you write to PNG from an instance of :class:`RgbRasterImage`, then no conversion has to be done, so the output is significantly faster.

GIF
^^^

The GIF format is selected by using the ``gif`` file extension.

.. code-block:: php

  $tux -> write("tux.gif");

This format is limited to using a 256-color palette.

- If your image is not an `IndexedRasterImage`, then it is indexed when you write.
- If the image uses more than 256 colors, then it will be converted to an 8-bit RGB representation (3 bits red, 3 bits green, 2 bits blue), which introduces some distortions.

When you are creating GIF images, then you can avoid these conversions by using a :class:`IndexedRasterImage` with a palette of fewer than 256 colors.

There is no encoder for multi-image GIF files at this stage.

BMP
^^^

The BMP format is selected by using the ``bmp`` file extension.

.. code-block:: php
  
  $tux -> write("tux.bmp");

This library will currently output BMP files as 24-bit uncompressed RGB files.

Netpbm Formats
^^^^^^^^^^^^^^

The Netpbm formats can be used for output. Each format is identified by their respective file extension:

.. code-block:: php

  $tux -> write("tux.ppm");
  $tux -> write("tux.pgm";
  $tux -> write("tux.pbm");

Since each of these formats has a different raster data representation, you should be aware that 

:PPM: For this output format, the file is converted to a :class:`RgbRasterImage` and typically written with a 24 bit color depth. In some cases, a 48 bit color depth will be used.
:PGM: The file is converted to a :class:`GrayscaleRasterImage` and written with a depth of 8 or 16 bits per pixel.
:PPM: The file is converted to a :class:`BlackAndWhiteRasterImage` and written with 1 bit per pixel.

If you want to avoid these conversions, then you should use the ``pnm`` extension to write your files. Since files with this extension can hold any of the above formats, the output encoder will avoid converting the raster data where possible.

  $tux -> write("tux.pnm");

