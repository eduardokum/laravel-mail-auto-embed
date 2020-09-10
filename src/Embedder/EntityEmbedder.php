<?php

namespace Eduardokum\LaravelMailAutoEmbed\Embedder;

use Eduardokum\LaravelMailAutoEmbed\Models\EmbeddableEntity;

interface EntityEmbedder
{
    /**
     * @param  EmbeddableEntity $entity
     * @return string
     */
    public function fromEntity(EmbeddableEntity $entity);
}
