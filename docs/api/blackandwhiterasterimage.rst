BlackAndWhiteRasterImage
========================

.. php:class:: BlackAndWhiteRasterImage

  .. php:method:: invert ()

  .. php:method:: clear ()

  .. php:method:: getWidth ()

      :returns: int

  .. php:method:: getHeight ()

      :returns: int

  .. php:method:: setPixel (int $x, int $y, int $value)

      :param int $x: X co-ordinate
      :param int $y: Y co-ordinate
      :param int $value: Value to set

  .. php:method:: getPixel (int $x, int $y)

      :param int $x: X co-ordinate
      :param int $y: Y co-ordinate
      :returns: int

  .. php:method:: toString ()

  .. php:method:: getRasterData ()

      :returns: string

  .. php:method:: mapColor (int $srcColor, RasterImage $destImage)

  .. php:method:: toRgb ()

      :returns: :class:`RgbRasterImage`

  .. php:method:: toGrayscale ()

      :returns: :class:`GrayscaleRasterImage`

  .. php:method:: toBlackAndWhite ()

  .. php:method:: toIndexed ()

      :returns: :class:`IndexedRasterImage`

  .. php:staticmethod:: create ($width, $height, array $data=null)

