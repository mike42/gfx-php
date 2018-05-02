IndexedRasterImage
==================

.. php:class:: IndexedRasterImage

  .. php:method:: getPalette ()

  .. php:method:: getRasterData ()

  .. php:method:: getHeight ()

  .. php:method:: getMaxVal ()

  .. php:method:: setPixel (int $x, int $y, int $value)

  .. php:method:: toRgb ()

  .. php:method:: toBlackAndWhite ()

  .. php:method:: toGrayscale ()

  .. php:method:: getPixel (int $x, int $y)

  .. php:method:: getWidth ()

  .. php:method:: toIndexed ()

  .. php:method:: indexToRgb (int $index)

  .. php:method:: rgbToIndex (array $rgb)

  .. php:method:: getTransparentColor ()

  .. php:method:: setTransparentColor (int $color=null)

  .. php:method:: setPalette (array $palette)

  .. php:method:: setMaxVal (int $maxVal)

  .. php:method:: allocateColor (array $color)

  .. php:method:: deallocateColor (array $color)

  .. php:staticmethod:: create (int $width, int $height, array $data=null, array $palette=null, int $maxVal=255)

