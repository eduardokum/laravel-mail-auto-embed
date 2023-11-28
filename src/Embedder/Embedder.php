<?php

namespace Eduardokum\LaravelMailAutoEmbed\Embedder;

use Eduardokum\LaravelMailAutoEmbed\Contracts\Embedder\UrlEmbedder;
use Eduardokum\LaravelMailAutoEmbed\Contracts\Embedder\EntityEmbedder;

abstract class Embedder implements UrlEmbedder, EntityEmbedder
{
}
