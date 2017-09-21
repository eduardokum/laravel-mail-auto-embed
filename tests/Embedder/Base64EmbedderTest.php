<?php

namespace Eduardokum\LaravelMailAutoEmbed\Tests\Embedder;

use Eduardokum\LaravelMailAutoEmbed\Embedder\Base64Embedder;
use Eduardokum\LaravelMailAutoEmbed\Tests\fixtures\PictureEntity;
use Eduardokum\LaravelMailAutoEmbed\Tests\TestCase;

class Base64EmbedderTest extends TestCase
{
    protected function setUp()
    {
        parent::setUp();

        $this->embedder = new Base64Embedder();
    }

    /**
     * @test
     */
    public function encodes_local_files_to_base64()
    {
        $embedder = new Base64Embedder();

        $result = $embedder->fromUrl('http://localhost/test.png');

        $this->assertStringStartsWith('data:image/png;base64,', $result);
    }

    /**
     * @test
     */
    public function encodes_entities_to_base64()
    {
        $embedder = new Base64Embedder();

        $picture = new PictureEntity();

        $result = $embedder->fromEntity($picture);

        $this->assertStringStartsWith('data:image/png;base64,', $result);
    }
}
