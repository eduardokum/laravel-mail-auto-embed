<?php

namespace Rsvpify\LaravelMailAutoEmbed\Embedder;

use Rsvpify\LaravelMailAutoEmbed\Models\EmbeddableEntity;

interface EntityEmbedder
{
    /**
     * @param  EmbeddableEntity $entity
     * @return string
     */
    public function fromEntity(EmbeddableEntity $entity);
}
