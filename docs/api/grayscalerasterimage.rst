GrayscaleRasterImage
====================

.. php:class:: GrayscaleRasterImage

  .. php:method:: getWidth ()

    Get the width of the image in pixels.

    :returns: int The width of the image in pixels.

  .. php:method:: getHeight ()

    Get the height of the image in pixels.

    :returns: int The height of the image in pixels.

  .. php:method:: setPixel (int $x, int $y, int $value)

    Set the value of a given pixel.

    :param int $x:
      X co-ordinate
    :param int $y:
      Y co-ordinate
    :param int $value:
      Value to set

  .. php:method:: getPixel (int $x, int $y)

    Get the value of a given pixel. The meaning of the integer value of this pixel is implementation-dependent.

    :param int $x:
      X co-ordinate
    :param int $y:
      Y co-ordinate
    :returns: int The value of the pixel at ($x, $y).

  .. php:method:: getMaxVal ()


  .. php:method:: getRasterData ()

    Get a binary string representing the underlying image data. The formatting of this data is implementation-dependent.

    :returns: string A binary string representation of the raster data for this image.

  .. php:method:: mapColor (int $srcColor, RasterImage $destImage)

    :param int $srcColor:
    :param $destImage:

  .. php:method:: toRgb ()

    Produce a copy of this :class:`RasterImage` in the RGB colorspace.

    :returns: :class:`RgbRasterImage` An RGB version of the image.

  .. php:method:: toGrayscale ()

    Produce a copy of this :class:`RasterImage` in a monochrome colorspace.

    :returns: :class:`GrayscaleRasterImage` A monochrome version of the image.

  .. php:method:: toBlackAndWhite ()

    Produce a copy of this :class:`RasterImage` in a pure black-and-white colorspace.

    :returns: :class:`BlackAndWhiteRasterImage` a black and white version of the image.

  .. php:method:: toIndexed ()

    Produce a copy of this :class:`RasterImage` as an indexed image with an associated palette of unique colors.

    :returns: :class:`IndexedRasterImage` An paletted version of the image.

  .. php:staticmethod:: create ($width, $height, array $data=null, $maxVal=255)

    :param $width:
    :param $height:
    :param array $data:
      Default: ``null``
    :param $maxVal:
      Default: ``255``

