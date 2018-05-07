RasterImage
===========

Generic interface to raster images.

.. php:interface:: RasterImage

  .. php:method:: getWidth () -> int

    Get the width of the image in pixels.

    :returns: int -- The width of the image in pixels.

  .. php:method:: getHeight () -> int

    Get the height of the image in pixels.

    :returns: int -- The height of the image in pixels.

  .. php:method:: getRasterData () -> string

    Get a binary string representing the underlying image data. The formatting of this data is implementation-dependent.

    :returns: string -- A binary string representation of the raster data for this image.

  .. php:method:: scale (int $width, int $height) -> RasterImage

    Produce a new :class:`RasterImage` based on this one. The new image will be scaled to the requested dimensions via resampling.

    :param int $width:
      The width of the returned image.
    :param int $height:
      The height of the returned image.
    :returns: :class:`RasterImage` -- A scaled version of the image.

  .. php:method:: write (string $filename)

    Write the image to a file. The output format is determined by the file extension.

    :param string $filename:
      Filename to write to.

  .. php:method:: toRgb () -> RgbRasterImage

    Produce a copy of this :class:`RasterImage` in the RGB colorspace.

    :returns: :class:`RgbRasterImage` -- An RGB version of the image.

  .. php:method:: getPixel (int $x, int $y) -> int

    Get the value of a given pixel. The meaning of the integer value of this pixel is implementation-dependent.

    :param int $x:
      X co-ordinate
    :param int $y:
      Y co-ordinate
    :returns: int -- The value of the pixel at ($x, $y).

  .. php:method:: setPixel (int $x, int $y, int $value)

    Set the value of a given pixel.

    :param int $x:
      X co-ordinate
    :param int $y:
      Y co-ordinate
    :param int $value:
      Value to set

  .. php:method:: toGrayscale () -> GrayscaleRasterImage

    Produce a copy of this :class:`RasterImage` in a monochrome colorspace.

    :returns: :class:`GrayscaleRasterImage` -- A monochrome version of the image.

  .. php:method:: toBlackAndWhite () -> BlackAndWhiteRasterImage

    Produce a copy of this :class:`RasterImage` in a pure black-and-white colorspace.

    :returns: :class:`BlackAndWhiteRasterImage` -- a black and white version of the image.

  .. php:method:: toIndexed () -> IndexedRasterImage

    Produce a copy of this :class:`RasterImage` as an indexed image with an associated palette of unique colors.

    :returns: :class:`IndexedRasterImage` -- An paletted version of the image.

