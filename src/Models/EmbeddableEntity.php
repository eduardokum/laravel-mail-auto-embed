<?php

namespace Eduardokum\LaravelMailAutoEmbed\Models;

interface EmbeddableEntity
{
    /**
     * @param  mixed  $id
     * @return EmbeddableEntity|null
     */
    public static function findEmbeddable($id);

    /**
     * @return mixed
     */
    public function getRawContent();

    /**
     * @return string
     */
    public function getFileName();

    /**
     * @return string
     */
    public function getMimeType();
}
