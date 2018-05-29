<?php

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
        if(strlen($chunk) !== $bytes) {
            throw new Exception("Unexpected end of file, needed $bytes but read $bytes");
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
          // Critical chunks
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
          // Refuse to return chunk
          throw new Exception("CRC did not match on $type chunk");
      }      
      return $chunk;
  }
  
  public function toString() {
      return $this -> type . " chunk";
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
if($chunk_header == null || $chunk_header -> getType() !== "IHDR") {
    throw new Exception("File does not begin with IHDR chunk");
}
$chunk_palette = null;
//$chunk_data = [];
$chunk_end = null;

while(( $chunk = PngChunk::fromBin($data) ) !== null) {
    if($chunk -> getType() === "IEND") {
        $chunk_end = $chunk;
        break;
    }
    echo $chunk -> toString() . "\n";
}

if($chunk_end === null) {
    throw new Exception("File does not end with IEND chunk");
}

if(!$data -> isEof()) {
    throw new Exception("Data extends past end of file");
}

