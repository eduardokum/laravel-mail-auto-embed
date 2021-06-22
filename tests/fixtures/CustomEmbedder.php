<?php

namespace Eduardokum\LaravelMailAutoEmbed\Tests\fixtures;

use Eduardokum\LaravelMailAutoEmbed\Embedder\AttachmentEmbedder;

class CustomEmbedder extends AttachmentEmbedder
{
    /**
     * @param  string  $url
     */
    public function fromUrl($url)
    {
        return "custom:{$url}";
    }
}
