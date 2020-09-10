<?php

namespace Eduardokum\LaravelMailAutoEmbed\Embedder;

interface UrlEmbedder
{
    /**
     * @param  string  $url
     * @return string
     */
    public function fromUrl($url);

    /**
     * @param  string  $path
     * @return string
     */
    public function fromPath($path);
}
