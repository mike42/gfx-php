RgbRasterImage
==============

.. php:class:: RgbRasterImage

  .. php:method:: getWidth ()

  .. php:method:: getHeight ()

  .. php:method:: getRasterData ()

  .. php:method:: getMaxVal ()

  .. php:method:: getPixel (int $x, int $y)

  .. php:method:: indexToRgb (int $val)

  .. php:method:: rgbToIndex (array $val)

  .. php:method:: setPixel (int $x, int $y, int $value)

  .. php:method:: mapColor (int $srcColor, RasterImage $destImage)

  .. php:method:: toRgb ()

  .. php:method:: toGrayscale ()

  .. php:method:: toBlackAndWhite ()

  .. php:method:: toIndexed ()

  .. php:staticmethod:: rgbToInt (int $r, int $g, int $b)

  .. php:staticmethod:: intToRgb ($in)

  .. php:staticmethod:: create ($width, $height, array $data=null, $maxVal=255)

  .. php:staticmethod:: convertDepth (&$item, $key, array $data)

