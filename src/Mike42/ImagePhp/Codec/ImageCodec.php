<?php
namespace Mike42\ImagePhp\Codec;

class ImageCodec
{
    protected static $instance = null;
    
    protected $encoders;
    
    protected $decoders;
    
    public function __construct(array $encoders, array $decoders)
    {
        $this -> encoders = $encoders;
        $this -> decoders = $decoders;
    }
    
    public function identify(string $blob) : string
    {
        // TODO
        return "image/x‑portable‑bitmap";
    }
    
    public function getDecoderForFormat(string $format) : ImageDecoder
    {
        // TODO
        return $this -> encoders[0];
    }

    public static function getInstance() : ImageCodec
    {
        if (self::$instance  === null) {
            $encoders = [
                PnmCodec::getInstance()
            ];
            $decoders = [
                PnmCodec::getInstance()
            ];
            self::$instance = new ImageCodec($encoders, $decoders);
        }
        return self::$instance;
    }
}
