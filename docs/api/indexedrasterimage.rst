IndexedRasterImage
==================

.. php:class:: IndexedRasterImage

  .. php:method:: getPalette ()

  .. php:method:: getRasterData ()

      :returns: string

  .. php:method:: getHeight ()

      :returns: int

  .. php:method:: getMaxVal ()

  .. php:method:: setPixel (int $x, int $y, int $value)

      :param int $x: X co-ordinate
      :param int $y: Y co-ordinate
      :param int $value: Value to set

  .. php:method:: toRgb ()

      :returns: :class:`RgbRasterImage`

  .. php:method:: toBlackAndWhite ()

  .. php:method:: toGrayscale ()

      :returns: :class:`GrayscaleRasterImage`

  .. php:method:: getPixel (int $x, int $y)

      :param int $x: X co-ordinate
      :param int $y: Y co-ordinate
      :returns: int

  .. php:method:: getWidth ()

      :returns: int

  .. php:method:: toIndexed ()

      :returns: :class:`IndexedRasterImage`

  .. php:method:: indexToRgb (int $index)

  .. php:method:: rgbToIndex (array $rgb)

  .. php:method:: getTransparentColor ()

  .. php:method:: setTransparentColor (int $color=null)

  .. php:method:: setPalette (array $palette)

  .. php:method:: setMaxVal (int $maxVal)

  .. php:method:: allocateColor (array $color)

  .. php:method:: deallocateColor (array $color)

  .. php:staticmethod:: create (int $width, int $height, array $data=null, array $palette=null, int $maxVal=255)

