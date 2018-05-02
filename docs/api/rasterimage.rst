RasterImage
===========

.. php:class:: RasterImage

  .. php:method:: getWidth ()

      :returns: int

  .. php:method:: getHeight ()

      :returns: int

  .. php:method:: getRasterData ()

      :returns: string

  .. php:method:: scale (int $width, int $height)

      :param int $width: The width of the returned image.
      :param int $height: The height of the returned image.
      :returns: :class:`RasterImage`

  .. php:method:: write (string $filename)

      :param string $filename: Filename to write to.

  .. php:method:: toRgb ()

      :returns: :class:`RgbRasterImage`

  .. php:method:: getPixel (int $x, int $y)

      :param int $x: X co-ordinate
      :param int $y: Y co-ordinate
      :returns: int

  .. php:method:: setPixel (int $x, int $y, int $value)

      :param int $x: X co-ordinate
      :param int $y: Y co-ordinate
      :param int $value: Value to set

  .. php:method:: toGrayscale ()

      :returns: :class:`GrayscaleRasterImage`

  .. php:method:: toBlackAndWhite ()

  .. php:method:: toIndexed ()

      :returns: :class:`IndexedRasterImage`

