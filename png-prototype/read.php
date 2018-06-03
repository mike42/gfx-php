<?php

require_once("../vendor/autoload.php");

use Mike42\GfxPhp\BlackAndWhiteRasterImage;
use Mike42\GfxPhp\GrayscaleRasterImage;
use Mike42\GfxPhp\RgbRasterImage;
use Mike42\GfxPhp\IndexedRasterImage;

/**
 * Takes 8-bit samples, and produces eight times as many 1-bit samples
 */
function expand_bytes_1bpp(array $in) {
    $res =  [];
    foreach($in as $byte) {
        $res[] = ($byte >> 7) & 0x01;
        $res[] = ($byte >> 6) & 0x01;
        $res[] = ($byte >> 5) & 0x01;
        $res[] = ($byte >> 4) & 0x01;
        $res[] = ($byte >> 3) & 0x01;
        $res[] = ($byte >> 2) & 0x01;
        $res[] = ($byte >> 1) & 0x01;
        $res[] = $byte & 0x01;
    }
    return $res;
}

/**
 * Takes 8-bit samples, and produces four times as many 2-bit samples
 */
function expand_bytes_2bpp(array $in) {
    $res =  [];
    foreach($in as $byte) {
        $res[] = ($byte >> 6) & 0x03;
        $res[] = ($byte >> 4) & 0x03;
        $res[] = ($byte >> 2) & 0x03;
        $res[] = ($byte) & 0x03;
    }
    return $res;
}

/**
 * Takes 8-bit samples, and produces twice as many 4-bit samples
 */
function expand_bytes_4bpp(array $in) {
    $res =  [];
    foreach($in as $byte) {
        $res[] = ($byte >> 4) & 0x0F;
        $res[] = ($byte & 0x0F);
    }
    return $res;
}

/**
 * Takes 8-bit samples, and produces half as many 16-bit samples.
 */
function combine_bytes(array $in) {
    $data = array_values(unpack("n*", pack("C*", ...$in)));
    return $data;
}

/**
 * We'll use this to mix with a background color.
 */
function alphaMix(array $data, $chunkSize, $maxVal) {
    // Will need to change to "alphaMixPixel" to [$this, "alphaMixPixel"] once we are in a class.
    $noAlphaPixels = array_map("alphaMixPixel", array_chunk($data, $chunkSize, false));
    return array_merge(...$noAlphaPixels);
}

function alphaMixPixel(array $pixels) {
    // Just drop Alpha completely for now.
    // TODO we need the maxVal and a background color in-scope here.
    array_pop($pixels);
    return $pixels;
}

function paethPredictor(int $a, int $b, int $c) {
    // Nearest-neighbor, based on pseudocode from the PNG spec.
    $p = $a + $b - $c;
    $pa = abs($p - $a);
    $pb = abs($p - $b);
    $pc = abs($p - $c);
    if($pa <= $pb && $pa <= $pc) {
        return $a;
    } else if($pb <= $pc) {
        return $b;
    }
    return $c;
}

function unfilter(array $currentFiltered, array $prior, int $filterType, int $bpp) {
  $lw = count($currentFiltered);
  if($filterType === 0) {
      // None
      return $currentFiltered;
  } elseif($filterType === 1) {
      $ret = array_fill(0, $lw, 128);
      for($i = 0; $i < $lw; $i++) {
          $rawLeft = ($i < $bpp ? 0 : $ret[$i-$bpp]);
          $subX = $currentFiltered[$i];
          $ret[$i] = ($subX + $rawLeft) % 256;
      }
      return $ret;
  } elseif($filterType === 2) {
      $ret = array_fill(0, $lw, 0);
      for($i = 0; $i < $lw; $i++) {
          $ret[$i] = ($currentFiltered[$i] + $prior[$i]) % 256;
      }
      return $ret;
  } elseif($filterType === 3) {
      $ret = array_fill(0, $lw, 0);
      
      for($i = 0; $i < $lw; $i++) {
          $prevX = $i < $bpp ? 0 : $ret[$i-$bpp];
          $priorX = $prior[$i];
          $avgX = intdiv($prevX + $priorX, 2);
          $prediction = $currentFiltered[$i] - $avgX;
          $ret[$i] = ($avgX + $currentFiltered[$i]) % 256;
      }
      return $ret;
  } elseif($filterType === 4) {
      $ret = array_fill(0, $lw, 0);
      for($i = 0; $i < $lw; $i++) {
          $upperLeft = $i < $bpp ? 0 : $prior[$i-$bpp];
          $left = $i < $bpp ? 0 : $ret[$i-$bpp];
          $upper = $prior[$i];
          $ret[$i] = (paethPredictor($left, $upper, $upperLeft) + $currentFiltered[$i]) % 256;
      }
      return $ret;
  }
  throw new Exception("Filter type $filterType not valid");
}

interface DataInputStream {
    public function read(int $bytes);
    public function isEof();
    public function peek(int $bytes);
    public function advance(int $bytes);
}

class DataBlobInputStream implements DataInputStream {
    public function __construct(string $data) {
        $this -> data = $data;
        $this -> offset = 0;
    }

    public function read(int $bytes) {
        $chunk = $this -> peek($bytes);
        $this -> advance($bytes);
        return $chunk;
    }

    public function advance(int $bytes) {
        $this -> offset += $bytes;
    }

    public function peek(int $bytes) {
        $chunk = substr($this -> data, $this -> offset, $bytes);
        if($chunk === false) {
            throw new Exception("End of file reached, cannot retrieve more data.");
        }
        $read = strlen($chunk);
        if($read !== $bytes) {
            throw new Exception("Unexpected end of file, needed $read but read $bytes");
        }
        return $chunk;
    }

    public function isEof() {
        return $this -> offset >= strlen($this -> data);
    }
 
    public static function fromBlob(string $blob) {
        return new DataBlobInputStream($blob);
    }
    
    public static function fromFilename(string $filename) {
        $blob = file_get_contents($filename);
        if($blob === false) {
            throw new Exception($filename);
        }
        return self::fromBlob($blob);
    }
}

/**
 * Reading PNG files in PHP.
 */
class PngChunk {
  private $type;
  private $data;
  private $crc;

  public function __construct(string $type, string $data) {
      $this -> type = $type;
      $this -> data = $data;
      // Always compute CRC based on the data we have.
      // If this is being read from a chunk's binary, then
      // this will be compared, if not, it will be written.
      $this -> crc = crc32($type . $data);
  }

  public function toBin() {
      $len = strlen($this -> data);
      $lenData = pack("N", $len);
      $bodyData = $this -> type . $this -> data;
      $crcData = pack("N", $this -> crc);
      return $lenData . $bodyData . $crcData;
  }
  
  public function getCrc() {
      return $this -> crc;
  }

  public function getType() {
      return $this -> type;
  }
  
  public function getData() {
      return $this -> data;
  }

  public static function isValidChunkName(string $name) {
      if(array_search($name, ["IHDR", "IDAT", "PLTE", "IEND"], true) !== false) {
          // Critical chunks defined as of PNG 1.2
          return true;
      } else if(preg_match("/[a-z][a-zA-Z]{3}/", $name)) {
          // Ancillary chunks
          return true;
      }
      return false;
  }

  public static function fromBin(DataInputStream $in) {
      if($in -> isEof()) {
          return null;
      }
      $lenData = $in -> read(4);
      $len = unpack("N", $lenData)[1];
      $type = $in -> read(4);
      if(!self::isValidChunkName($type)) {
          // In case this is not a real chunk, we don't want
          // to use random binary data in error messages later.
          throw new Exception("Bad chunk name");
      }
      $data = $in -> read($len);
      $crcData = $in -> read(4);
      $crc = unpack("N", $crcData)[1];
      $chunk = new PngChunk($type, $data);
      if($crc != $chunk -> getCrc()) {
          // Refuse to return chunk with bad checksum
          throw new Exception("CRC did not match on $type chunk");
      }      
      return $chunk;
  }
  
  public function toString() {
      return $this -> type . " chunk";
  }
}

class PngHeader {
  const HEADER_SIZE = 13;
  const COLOR_TYPE_MONOCHROME = 0;
  const COLOR_TYPE_RGB = 2;
  const COLOR_TYPE_INDEXED = 3;
  const COLOR_TYPE_MONOCHROME_ALPHA = 4;
  const COLOR_TYPE_RGBA = 6;

  const COMPRESSION_DEFLATE = 0;

  const INTERLACE_NONE = 0;
  const INTERLACE_ADAM7 = 1;

  private $width;
  private $height;
  private $bitDepth;
  private $colorType;
  private $compression;
  private $filter;
  private $interlace;

  public function __construct(int $width, int $height, int $bitDepth, int $colorType, int $compression, int $filter, int $interlace) {
    // Image dimensions
    if($width < 1 || $width > 2147483647 ||
      $height < 1 || $height > 2147483647) {
        throw new Exception("Invalid image dimensions");
    }
    $this -> width = $width;
    $this -> height = $height;
    // Color type & bit depth    
    // - Only some combinations of bit depth and colorType are valid
    $this -> bitDepth = $bitDepth;
    $this -> colorType = $colorType;
    // Compression
    if($compression != PngHeader::COMPRESSION_DEFLATE) {
        throw new Exception("Compression type not supported");
    }
    $this -> compression = $compression;
    // Filter type set
    if($filter != 0) {
        throw new Exception("Filter type set not supported");
    }
    $this -> filter = $filter;
    // Interlace method
    if($interlace != PngHeader::INTERLACE_NONE && 
        $interlace != PngHeader::INTERLACE_ADAM7) {
        throw new Exception("Interlace method not supported");
    }
    $this -> interlace = $interlace;
  }

  public static function fromChunk(PngChunk $chunk) {
    $chunkData = $chunk -> getData();
    $chunkLen = strlen($chunkData);
    if($chunkLen !== PngHeader::HEADER_SIZE) {
      throw new Exception("Header must be " . PngHeader::HEADER_SIZE . " bytes, but got $chunkLen bytes.");
    }
    // Unpack binary
    $dataItems = unpack("Nwidth/Nheight/CbitDepth/CcolorType/Ccompression/Cfilter/Cinterlace", $chunkData);
    // Construct
    return new PngHeader($dataItems['width'], $dataItems['height'], $dataItems['bitDepth'],$dataItems['colorType'], $dataItems['compression'], $dataItems['filter'], $dataItems['interlace']);
  }

  public function toString() {
      return "Image dimensions " . $this -> width . " x " . $this -> height .
          ", bitDepth " . $this -> bitDepth .
          ", colorType " . $this -> colorType .
          ", compression " . $this -> compression .
          ", filter " . $this -> filter .
          ", interlace " . $this -> interlace; 
  }

  public function allowsPalette() {
      return $this -> requiresPalette() ||
          $this -> colorType === PngHeader::COLOR_TYPE_RGB ||
          $this -> colorType === PngHeader::COLOR_TYPE_RGBA;
  }

  public function requiresPalette() {
      return $this -> colorType === PngHeader::COLOR_TYPE_INDEXED;
  }

  public function getWidth() {
    return $this -> width;
  }

  public function getHeight() {
    return $this -> height;
  }

  public function getBitDepth() {
    return $this -> bitDepth;
  }

  public function getColorType() {
    return $this -> colorType;
  }

  public function getCompresssion() {
    return $this -> compresssion;
  }

  public function getFilter() {
    return $this -> filter;
  }

  public function getInterlace() {
    return $this -> interlace;
  }
}

const PNG_SIGNATURE="\x89\x50\x4E\x47\x0D\x0A\x1A\x0A";

$fn = $argv[1];

echo "Testing $fn\n";
$data = DataBlobInputStream::fromFilename($argv[1]);

// Check signature
$signature = $data -> read(8);
if($signature != PNG_SIGNATURE) {
    throw new Exception("Bad PNG signature");
}

// Iterate chunks
$chunk_header = PngChunk::fromBin($data);
$header = PngHeader::fromChunk($chunk_header);
if($chunk_header == null || $chunk_header -> getType() !== "IHDR") {
    throw new Exception("File does not begin with IHDR chunk");
}
echo $chunk_header -> toString() . "\n";
echo $header -> toString() . "\n";
$chunk_palette = null;
$chunk_data = [];
$chunk_end = null;

while(( $chunk = PngChunk::fromBin($data) ) !== null) {
    echo $chunk -> toString() . "\n";
    if($chunk -> getType() === "IEND") {
        $chunk_end = $chunk;
        break;
    }
    if($chunk -> getType() === "PLTE") {
        if(!$header -> allowsPalette()) {
            throw new Exception("Palette not allowed for this image type");
        } else if($chunk_palette !== null) {
            throw new Exception("Multiple palette entries");
        } else if(count($chunk_data) > 0) {
            throw new Exception("Palette must be issued before first data chunk");
        }
        $paletteLen = strlen($chunk -> getData());
        if($paletteLen === 0 || $paletteLen > (256 * 3) || $paletteLen % 3 !== 0) {
            throw new Exception("Palette length is invalid");
        }
        $chunk_palette = $chunk;
    }
    if($chunk -> getType() === "IDAT") {
        $chunk_data[] = $chunk;
    }
}

if($header -> requiresPalette() && $chunk_palette === null) {
    throw new Exception("Missing palette, required for this image type");
}

if(count($chunk_data) === 0) {
    throw new Exception("No data received");
}

if($chunk_end === null) {
    throw new Exception("File does not end with IEND chunk");
}

if(!$data -> isEof()) {
    throw new Exception("Data extends past end of file");
}

// Extract, decompress and join chunks
$binData = '';
foreach($chunk_data as $chunk) {
    // TODO maximum decoded data size can be determined from image size and bit
    // depth
    $chunkDataDecompressed = zlib_decode($chunk -> getData());
    if($chunkDataDecompressed === false) {
        throw new Exception("DEFLATE decompression failed");
    }
    $binData .= $chunkDataDecompressed;
}

// Note ADAM7 interlace alters the length, making this next step invalid
if($header -> getInterlace() !== PngHeader::INTERLACE_NONE) {
    throw new Exception("Interlace not implemented.");
}

// Turn into array of scan-lines based on filtering
$bitDepth = $header -> getBitDepth();
$width = $header -> getWidth();
$height = $header -> getHeight();
$channelLookup = [
    PngHeader::COLOR_TYPE_MONOCHROME => 1,
    PngHeader::COLOR_TYPE_RGB => 3,
    PngHeader::COLOR_TYPE_INDEXED => 1,
    PngHeader::COLOR_TYPE_MONOCHROME_ALPHA => 2,
    PngHeader::COLOR_TYPE_RGBA => 4,
];
$channels = $channelLookup[$header -> getColorType()];
$scanlineBytes = intdiv($width * $bitDepth + 7, 8) * $channels;

// Extract filtered data
$scanlinesWithFiltering = str_split($binData, $scanlineBytes  + 1);
$filterType = [];
$filteredData = [];
foreach($scanlinesWithFiltering as $scanline) {
    $filterType[] = ord($scanline[0]);
    $filteredData[] = array_values(unpack("C*", substr($scanline, 1)));
}

// Transform back to raw data
$rawData = [];
$bytesPerPixel = intdiv($bitDepth + 7, 8) * $channels;
$prior = array_fill(0, $scanlineBytes, 0);
foreach($filteredData as $key => $currentFiltered) {
    $current = unfilter($currentFiltered, $prior, $filterType[$key], $bytesPerPixel);
    $imgScanlineData[] = $current;
    $prior = $current;
}
$imageData = array_merge(...$imgScanlineData);

// Further processing depends on image type
switch($header -> getColorType()) {
    case PngHeader::COLOR_TYPE_MONOCHROME:
        switch($bitDepth) {
            case 1:
                $im = BlackAndWhiteRasterImage::create($width, $height, $imageData);
                $im -> invert(); // Difference in meaning for set/unset pixels.
                break;
            case 2:
              // Re-interpret data with lower depth (2 bits per sample);
              $expandedData = expand_bytes_2bpp($imageData);
              $im = GrayscaleRasterImage::create($width, $height, $expandedData, 0x03);
              break;
            case 4:
              // Re-interpret data with lower depth (4 bits per sample);
              $expandedData = expand_bytes_4bpp($imageData);
              $im = GrayscaleRasterImage::create($width, $height, $expandedData, 0x0F);
              break;
            case 8:
              $im = GrayscaleRasterImage::create($width, $height, $imageData);
              break;
            case 16:
              // Re-interpret data with higher depth.
              $combinedData = combine_bytes($imageData);
              $im = GrayscaleRasterImage::create($width, $height, $combinedData, 65535);
              break;
            default:
              throw new Exception("COLOR_TYPE_MONOCHROME at bit depth $bitDepth not supported");
        }
        break;
    case PngHeader::COLOR_TYPE_RGB:
        switch($bitDepth) {
            case 8:
              $im = RgbRasterImage::create($width, $height, $imageData);
              break;
            case 16:
              // Re-interpret data with higher depth.
              $combinedData = combine_bytes($imageData);
              $im = RgbRasterImage::create($width, $height, $combinedData, 0xFFFF);
              break;
            default:
              throw new Exception("COLOR_TYPE_RGB at bit depth $bitDepth not supported");
        }
        break;
    case PngHeader::COLOR_TYPE_INDEXED:
        switch($bitDepth) {
            case 1:
              $imageData = expand_bytes_1bpp($imageData);
              break;
            case 2:
              $imageData = expand_bytes_2bpp($imageData);
              break;
            case 4:
              $imageData = expand_bytes_4bpp($imageData);
              break;
            case 8:
              // Data is all good.
              break;
            default:
              throw new Exception("COLOR_TYPE_INDEXED at bit depth $bitDepth not supported");
        }
        $paletteArr = array_values(unpack("C*", $chunk_palette -> getData()));
        $palette = array_chunk($paletteArr, 3);
        $im = IndexedRasterImage::create($width, $height, $imageData, $palette, 0xFF);
        break;
    case PngHeader::COLOR_TYPE_MONOCHROME_ALPHA:
        // Mix out Alpha and load as Grayscale.
        switch($bitDepth) {
            case 8:
              $mixedData = alphaMix($imageData, 2, 0xFF);
              $im = GrayscaleRasterImage::create($width, $height, $mixedData, 0xFF);
              break;
            case 16:
              $mixedData = alphaMix(combine_bytes($imageData), 2, 0xFFFF);
              $im = GrayscaleRasterImage::create($width, $height, $mixedData, 0xFFFF);
              break;
            default:
              throw new Exception("COLOR_TYPE_MONOCHROME_ALPHA at bit depth $bitDepth not supported");
        }
        break;
    case PngHeader::COLOR_TYPE_RGBA:
        // Mix out Alpha and load as RGB.
        switch($bitDepth) {
            case 8:
              $mixedData = alphaMix($imageData, 4, 0xFF);
              $im = RgbRasterImage::create($width, $height, $mixedData, 0xFF);
              break;
            case 16:
              $mixedData = alphaMix(combine_bytes($imageData), 4, 0xFFFF);
              $im = RgbRasterImage::create($width, $height, $mixedData, 0xFFFF);
              break;
            default:
              throw new Exception("COLOR_TYPE_RGBA at bit depth $bitDepth not supported");
        }
        break;
    default:
         throw new Exception("Unsupported image type");
}
$im -> write('out/' . basename($argv[1], '.png') . ".ppm");
echo $im -> toBlackAndWhite() -> toString() . "\n";
exit(0);

