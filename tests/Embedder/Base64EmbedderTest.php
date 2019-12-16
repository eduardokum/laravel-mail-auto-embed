<?php

namespace Rsvpify\LaravelMailAutoEmbed\Tests\Embedder;

use Rsvpify\LaravelMailAutoEmbed\Embedder\Base64Embedder;
use Rsvpify\LaravelMailAutoEmbed\Tests\fixtures\PictureEntity;
use Rsvpify\LaravelMailAutoEmbed\Tests\TestCase;

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
