The pure PHP image processing library
=====================================

This library implements input, output and processing of raster images in pure
PHP, so that image processing PHP extensions are not required.

This allows developers to eliminate some portability issues from their applications.

The basic usage is like this:

.. code-block:: php
   
   <?php
   use Mike42\ImagePhp\Image;
   $img = Image::fromFile("colorwheel256.ppm");
   $img -> write("test.gif");

Features
--------

- Format support includes PNG, GIF, BMP and the Netpbm formats.
- Support for scaling, cropping, format conversion and colorspace transformations.
- Pure PHP: This library does not require Gd, ImageMagick or GraphicsMagick extensions.

Installation
------------

Install image-php by running:

.. code-block:: bash

    composer install mike42/image-php

Contribute
----------

- Issue Tracker: https://github.com/mike42/image-php/issues
- Source Code: https://github.com/mike42/image-php

Navigation
==========

.. toctree::
   :maxdepth: 1
   :caption: User Documentation

   user/formats.rst
   user/imagetypes.rst
   user/operations.rst

.. toctree::
   :maxdepth: 1
   :caption: Project Information
   
   project/license.rst
   project/contributing.rst

.. toctree::
   :maxdepth: 2
   :caption: API Documentation

   Classes <api.rst>

Indices and tables
==================

* :ref:`genindex`
* :ref:`search`
