<?php
namespace Mike42\GfxPhp\Codec\Gif;

use Mike42\GfxPhp\Codec\Common\DataInputStream;
use Mike42\GfxPhp\IndexedRasterImage;
use Mike42\GfxPhp\RasterImage;
use Mike42\GfxPhp\Util\LzwCompression;
use mysql_xdevapi\Exception;

class GifDataStream
{
    const GIF87_SIGNATURE="GIF87a";
    const GIF89_SIGNATURE="GIF89a";
    const GIF_TRAILER="\x3B";

    private $header;
    private $logicalScreen;
    private $data;
    private $trailer;

    private function __construct(string $header, GifLogicalScreen $logicalScreen, array $data, string $trailer)
    {
        $this -> header = $header;
        $this -> logicalScreen = $logicalScreen;
        $this -> data = $data;
        $this -> trailer = $trailer;
    }

    public static function fromBinary(DataInputStream $data) : GifDataStream
    {
        // Check header
        $header = $data -> read(6);
        if ($header != GifDataStream::GIF87_SIGNATURE && $header != GifDataStream::GIF89_SIGNATURE) {
            throw new \Exception("Bad GIF header");
        }
        $logicalScreen = GifLogicalScreen::fromBin($data);
        $imageData = [];
        while ($data -> peek(1) != GifDataStream::GIF_TRAILER) {
            $imageData[] = GifData::fromBin($data);
        }
        $trailer = $data -> read(1); // Discard trailer byte
        if (!$data -> isEof()) {
            throw new \Exception("The GIF file is corrupt; data found after the GIF trailer");
        }
        return new GifDataStream($header, $logicalScreen, $imageData, $trailer);
    }

    public function toRasterImage(int $imageIndex = 0) : IndexedRasterImage
    {
        // Extract an image from the GIF
        $currentIndex = 0;
        foreach ($this -> data as $dataBlock) {
            if ($dataBlock -> getGraphicsBlock() !== null && $dataBlock -> getGraphicsBlock() -> getTableBasedImage() != null) {
                // This is a raster image
                if ($currentIndex == $imageIndex) {
                    return GifDataStream::extractImage($this -> logicalScreen, $dataBlock -> getGraphicsBlock() -> getTableBasedImage());
                }
                $currentIndex++;
            }
        }
        throw new \Exception("Could not find image #$imageIndex in GIF file");
    }

    private static function extractImage(GifLogicalScreen $logicalScreen, GifTableBasedImage $tableBasedImage) : IndexedRasterImage
    {
        $width = $tableBasedImage -> getImageDescriptor() -> getWidth();
        $height = $tableBasedImage -> getImageDescriptor() -> getHeight();
        $colorTable = $tableBasedImage -> getLocalColorTable() == null ? $logicalScreen -> getGlobalColorTable() : $tableBasedImage -> getLocalColorTable();
        if ($colorTable == null) {
            throw new \Exception("GIF contains no color table for the image. Loading this type of file is not supported.");
        }
        if ($width == 0 || $height == 0) {
            throw new \Exception("GIF contains no pixels. Loading this type of file is not supported.");
        }
       // De-compress the actual image data
        $compressedData = join($tableBasedImage ->getDataSubBlocks());
        $decompressedData = LzwCompression::decompress($compressedData, $tableBasedImage -> getLzqMinSize());
       // Array of ints for IndexedRasterImage
        $dataArr = array_values(unpack("C*", $decompressedData));
        return IndexedRasterImage::create($width, $height, $dataArr, $colorTable -> getPalette());
    }
}
