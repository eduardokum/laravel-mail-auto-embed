<?php

namespace Rsvpify\LaravelMailAutoEmbed\Tests\Embedder;

use Rsvpify\LaravelMailAutoEmbed\Tests\TestCase;
use Rsvpify\LaravelMailAutoEmbed\Embedder\Base64Embedder;
use Rsvpify\LaravelMailAutoEmbed\Tests\fixtures\PictureEntity;

class Base64EmbedderTest extends TestCase
{
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

    protected function setUp()
    {
        parent::setUp();

        $this->embedder = new Base64Embedder();
    }
}
