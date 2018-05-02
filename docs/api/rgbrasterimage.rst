RgbRasterImage
==============

.. php:class:: RgbRasterImage

  .. php:method:: getWidth ()

      :returns: int
  .. php:method:: getHeight ()

      :returns: int
  .. php:method:: getRasterData ()

      :returns: string
  .. php:method:: getMaxVal ()

  .. php:method:: getPixel (int $x, int $y)

      :param int $x: X co-ordinate
      :param int $y: Y co-ordinate
      :returns: int
  .. php:method:: indexToRgb (int $val)

  .. php:method:: rgbToIndex (array $val)

  .. php:method:: setPixel (int $x, int $y, int $value)

      :param int $x: X co-ordinate
      :param int $y: Y co-ordinate
      :param int $value: Value to set

  .. php:method:: mapColor (int $srcColor, RasterImage $destImage)

  .. php:method:: toRgb ()

      :returns: :class:`RgbRasterImage`
  .. php:method:: toGrayscale ()

      :returns: :class:`GrayscaleRasterImage`
  .. php:method:: toBlackAndWhite ()

  .. php:method:: toIndexed ()

      :returns: :class:`IndexedRasterImage`
  .. php:staticmethod:: rgbToInt (int $r, int $g, int $b)

  .. php:staticmethod:: intToRgb ($in)

  .. php:staticmethod:: create ($width, $height, array $data=null, $maxVal=255)

  .. php:staticmethod:: convertDepth (&$item, $key, array $data)

