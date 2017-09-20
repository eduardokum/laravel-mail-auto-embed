<?php

namespace Eduardokum\LaravelMailAutoEmbed\Tests\fixtures;

use Eduardokum\LaravelMailAutoEmbed\Models\EmbeddableEntity;

class PictureEntity implements EmbeddableEntity
{
    /**
     * @param  mixed  $id
     * @return EmbeddableEntity|null
     */
    public static function findEmbeddable($id)
    {
        if ($id == '1') {
            return new static();
        }

        return null;
    }

    /**
     * @return mixed
     */
    public function getRawContent()
    {
        return file_get_contents(public_path('test.png'));
    }

    /**
     * @return string
     */
    public function getFileName()
    {
        return 'test.png';
    }

    /**
     * @return string
     */
    public function getMimeType()
    {
        return 'image/png';
    }
}
