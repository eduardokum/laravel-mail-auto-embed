<?php

namespace Eduardokum\LaravelMailAutoEmbed\Tests\Embedder;

use Eduardokum\LaravelMailAutoEmbed\Embedder\Base64Embedder;
use Eduardokum\LaravelMailAutoEmbed\Tests\fixtures\PictureEntity;
use Eduardokum\LaravelMailAutoEmbed\Tests\TestCase;

class Base64EmbedderTest extends TestCase
{
    /**
     * @before
     * @return void
     */
    protected function setUpEmbedder(): void
    {
        $this->embedder = new Base64Embedder();
    }

    /**
     * @test
     */
    public function testLocalConversion()
    {
        $embedder = new Base64Embedder();

        $result = $embedder->fromUrl('http://localhost/test.png');

        $this->assertStringStartsWith('data:image/png;base64,', $result);
    }

    /**
     * @test
     */
    public function testEntityConversion()
    {
        $embedder = new Base64Embedder();

        $picture = new PictureEntity();

        $result = $embedder->fromEntity($picture);

        $this->assertStringStartsWith('data:image/png;base64,', $result);
    }
}
