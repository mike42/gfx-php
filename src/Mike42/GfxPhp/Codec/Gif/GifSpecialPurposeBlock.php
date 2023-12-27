<?php
declare(strict_types=1);

namespace Mike42\GfxPhp\Codec\Gif;

class GifSpecialPurposeBlock
{

    private ?GifApplicationExt $applicationExt;
    private ?GifCommentExt $commentExt;

    public function __construct(GifApplicationExt $applicationExt = null, GifCommentExt $commentExt = null)
    {
        $this->applicationExt = $applicationExt;
        $this->commentExt = $commentExt;
    }

    public function getApplicationExt(): ?GifApplicationExt
    {
        return $this->applicationExt;
    }

    public function getCommentExt(): ?GifCommentExt
    {
        return $this->commentExt;
    }
}
