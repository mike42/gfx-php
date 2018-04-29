The pure PHP image processing library
=====================================

.. toctree::
   :maxdepth: 2
   :caption: Contents:

   API Documentation <api.rst>

.. code-block:: php
   
   <?php
   use Mike42\ImagePhp\Image;
   $img = Image::fromFile("colorwheel256.ppm");
   $img -> write("test.gif");

Indices and tables
==================

* :ref:`genindex`
* :ref:`modindex`
* :ref:`search`
