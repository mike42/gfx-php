AbstractRasterImage
===================

.. php:class:: AbstractRasterImage

  .. php:method:: rect ($startX, $startY, $width, $height[, $filled, $outline, $fill])

    Produce a rectangle with the given properties.

    :param $startX:
    :param $startY:
    :param $width:
    :param $height:
    :param $filled:
      Default: ``false``
    :param $outline:
      Default: ``1``
    :param int $fill:
      Default: ``1``

  .. php:method:: write (string $filename)

    Write the image to a file. The output format is determined by the file extension.

    :param string $filename:
      Filename to write to.

  .. php:method:: scale (int $width, int $height) -> RasterImage

    Produce a new :class:`RasterImage` based on this one. The new image will be scaled to the requested dimensions via resampling.

    :param int $width:
      The width of the returned image.
    :param int $height:
      The height of the returned image.
    :returns: :class:`RasterImage` -- A scaled version of the image.

  .. php:method:: subImage (int $startX, int $startY, int $width, int $height)

    :param int $startX:
    :param int $startY:
    :param int $width:
    :param int $height:

  .. php:method:: compose (RasterImage $source, int $startX, int $startY, int $destStartX, int $destStartY, int $width, int $height)

    :param RasterImage $source:
    :param int $startX:
    :param int $startY:
    :param int $destStartX:
    :param int $destStartY:
    :param int $width:
    :param int $height:

