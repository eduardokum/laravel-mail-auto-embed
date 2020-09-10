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

    /**
     * @before
     * @return void
     */
    protected function setUpEmbedder()
    {
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
    public function testLocalConversion()
    {
        $result = $this->embedder->fromUrl('http://localhost/test.png');

        $this->assertStringStartsWith('cid:', $result);

        $this->assertEquals(1, $this->getEmbeddedFilesCount());
    }

    /**
     * @test
     */
    public function testEntityConversion()
    {
        $picture = new PictureEntity();

        $result = $this->embedder->fromEntity($picture);

        $this->assertStringStartsWith('cid:', $result);

        $this->assertEquals(1, $this->getEmbeddedFilesCount());
    }
}
