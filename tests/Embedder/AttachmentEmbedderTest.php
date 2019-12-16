<?php

namespace Rsvpify\LaravelMailAutoEmbed\Tests\Embedder;

use Swift_Message;
use Swift_EmbeddedFile;
use Rsvpify\LaravelMailAutoEmbed\Tests\TestCase;
use Rsvpify\LaravelMailAutoEmbed\Embedder\AttachmentEmbedder;
use Rsvpify\LaravelMailAutoEmbed\Tests\fixtures\PictureEntity;

class AttachmentEmbedderTest extends TestCase
{
    /** @var Swift_Message */
    private $message;

    /** @var AttachmentEmbedder */
    private $embedder;

    /**
     * @test
     */
    public function whitelisted_domains_return_image()
    {
        config(['mail-auto-embed.whitelist' => [
            'http://example.com',
        ]]);

        $this->assertNotNull(app(AttachmentEmbedder::class)->fromRemoteUrl('http://example.com'));
    }

    /**
     * @test
     */
    public function domains_must_be_whitelisted()
    {
        config(['mail-auto-embed.whitelist' => [
            'http://example.com',
        ]]);

        $this->assertNull(app(AttachmentEmbedder::class)->fromRemoteUrl('http://not-whitelisted.com'));
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
}
