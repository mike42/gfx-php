File formats
============

Input formats
-------------

Files are read from a file by using the `Image::fromFile()` function:

.. code-block:: php
  
  use Mike42\ImagePhp\Image;
  $tux = Image::fromFile("tux.pbm")

If the image is not in a file, then `Image::fromBlob()` can load it insted:

.. code-block:: php

  use Mike42\ImagePhp\Image;
  $tuxStr = "...";
  $tux = Image::fromBlob($tuxStr, "tux.pbm");

In either case, the input format is determined using the file's `magic number`_.

.. _magic number: https://en.wikipedia.org/wiki/Magic_number_(programming)

NetPBM Formats
^^^^^^^^^^^^^^



Output formats
--------------

PNG
^^^

GIF
^^^

BMP
^^^

NetPBM Formats
^^^^^^^^^^^^^^

