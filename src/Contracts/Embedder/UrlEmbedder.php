<?php

namespace Eduardokum\LaravelMailAutoEmbed\Contracts\Embedder;

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

/**
     * @param string $base64
     * @return string
     */
    public function fromBase64($base64);
}
