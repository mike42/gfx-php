IndexedRasterImage
==================

.. php:class:: IndexedRasterImage

  .. php:method:: getPalette ()


  .. php:method:: getRasterData () -> string

    Get a binary string representing the underlying image data. The formatting of this data is implementation-dependent.

    :returns: string -- A binary string representation of the raster data for this image.

  .. php:method:: getHeight () -> int

    Get the height of the image in pixels.

    :returns: int -- The height of the image in pixels.

  .. php:method:: getMaxVal ()


  .. php:method:: setPixel (int $x, int $y, int $value)

    Set the value of a given pixel.

    :param int $x:
      X co-ordinate
    :param int $y:
      Y co-ordinate
    :param int $value:
      Value to set

  .. php:method:: toRgb () -> RgbRasterImage

    Produce a copy of this :class:`RasterImage` in the RGB colorspace.

    :returns: :class:`RgbRasterImage` -- An RGB version of the image.

  .. php:method:: toBlackAndWhite () -> BlackAndWhiteRasterImage

    Produce a copy of this :class:`RasterImage` in a pure black-and-white colorspace.

    :returns: :class:`BlackAndWhiteRasterImage` -- a black and white version of the image.

  .. php:method:: toGrayscale () -> GrayscaleRasterImage

    Produce a copy of this :class:`RasterImage` in a monochrome colorspace.

    :returns: :class:`GrayscaleRasterImage` -- A monochrome version of the image.

  .. php:method:: getPixel (int $x, int $y) -> int

    Get the value of a given pixel. The meaning of the integer value of this pixel is implementation-dependent.

    :param int $x:
      X co-ordinate
    :param int $y:
      Y co-ordinate
    :returns: int -- The value of the pixel at ($x, $y).

  .. php:method:: getWidth () -> int

    Get the width of the image in pixels.

    :returns: int -- The width of the image in pixels.

  .. php:method:: toIndexed () -> IndexedRasterImage

    Produce a copy of this :class:`RasterImage` as an indexed image with an associated palette of unique colors.

    :returns: :class:`IndexedRasterImage` -- An paletted version of the image.

  .. php:method:: indexToRgb (int $index)

    :param int $index:

  .. php:method:: rgbToIndex (array $rgb)

    :param array $rgb:

  .. php:method:: getTransparentColor ()


  .. php:method:: setTransparentColor ([])

    :param int $color:
      Default: ``null``

  .. php:method:: setPalette (array $palette)

    :param array $palette:

  .. php:method:: setMaxVal (int $maxVal)

    :param int $maxVal:

  .. php:method:: allocateColor (array $color)

    :param array $color:

  .. php:method:: deallocateColor (array $color)

    :param array $color:

  .. php:staticmethod:: create (int $width, int $height[, array $data, array $palette, int $maxVal])

    :param int $width:
    :param int $height:
    :param array $data:
      Default: ``null``
    :param array $palette:
      Default: ``null``
    :param int $maxVal:
      Default: ``255``

