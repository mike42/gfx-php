AbstractRasterImage
===================

.. php:class:: AbstractRasterImage

  .. php:method:: rect ($startX, $startY, $width, $height, $filled=false, $outline=1, $fill=1)

  .. php:method:: write (string $filename)

      :param string $filename: Filename to write to.

  .. php:method:: scale (int $width, int $height)

      :param int $width: The width of the returned image.
      :param int $height: The height of the returned image.
      :returns: :class:`RasterImage`

  .. php:method:: subImage (int $startX, int $startY, int $width, int $height)

  .. php:method:: compose (RasterImage $source, int $startX, int $startY, int $destStartX, int $destStartY, int $width, int $height)

