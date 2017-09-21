<?php

namespace Eduardokum\LaravelMailAutoEmbed\Tests\Embedder;

use Eduardokum\LaravelMailAutoEmbed\Embedder\AttachmentEmbedder;
use Eduardokum\LaravelMailAutoEmbed\Tests\fixtures\PictureEntity;
use Eduardokum\LaravelMailAutoEmbed\Tests\TestCase;
use Swift_EmbeddedFile;
use Swift_Message;

class AttachmentEmbedderTest extends TestCase
{
    /** @var  Swift_Message */
    private $message;

    /** @var  AttachmentEmbedder */
    private $embedder;

    protected function setUp()
    {
        parent::setUp();

        $this->message = new Swift_Message();
        $this->embedder = new AttachmentEmbedder($this->message);
    }

    /**
     * @return int
     */
    private function getEmbeddedFilesCount()
    {
        return collect($this->message->getChildren())
            ->filter(
                function ($item) {
                    return $item instanceof Swift_EmbeddedFile;
                }
            )
            ->count();
    }

    /**
     * @test
     */
    public function encodes_local_files_to_base64()
    {
        $result = $this->embedder->fromUrl('http://localhost/test.png');

        $this->assertStringStartsWith('cid:', $result);

        $this->assertEquals(1, $this->getEmbeddedFilesCount());
    }

    /**
     * @test
     */
    public function encodes_entities_to_base64()
    {
        $picture = new PictureEntity();

        $result = $this->embedder->fromEntity($picture);

        $this->assertStringStartsWith('cid:', $result);

        $this->assertEquals(1, $this->getEmbeddedFilesCount());
    }
}
