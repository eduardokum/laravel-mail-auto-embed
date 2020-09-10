<?php

namespace Eduardokum\LaravelMailAutoEmbed\Tests;

use Eduardokum\LaravelMailAutoEmbed\Listeners\SwiftEmbedImages;
use Eduardokum\LaravelMailAutoEmbed\Tests\Traits\InteractsWithSwift;

class MailTest extends TestCase
{
    use InteractsWithSwift;

    /**
     * @test
     */
    public function testAutomaticAttachmentConversion()
    {
        $message = $this->handleBeforeSendPerformedEvent('embed-when-enabled.html', [
            'enabled' => true,
            'method' => 'attachment'
        ]);

        $this->assertEmailImageTags([
            'url' => 'cid:',
            'entity' => 'cid:',
        ], $message->getBody());
    }

    /**
     * @test
     */
    public function testSkippedConversions()
    {
        $message = $this->handleBeforeSendPerformedEvent('embed-can-skip.html', [
            'enabled' => true,
            'method' => 'attachment'
        ]);

        $this->assertEmailImageTags([
            'embed' => 'cid:',
            'skip' => 'http://localhost/test.png',
        ], $message->getBody());
    }

    /**
     * @test
     */
    public function testManualConversions()
    {
        $message = $this->handleBeforeSendPerformedEvent('manual-embed-when-disabled.html', [
            'enabled' => false,
            'method' => 'attachment'
        ]);

        $this->assertEmailImageTags([
            'ignore' => 'http://localhost/test.png',
            'embed' => 'cid:',
        ], $message->getBody());
    }

    /**
     * @test
     */
    public function testOverrideTypeBase64()
    {
        $message = $this->handleBeforeSendPerformedEvent('override-to-base64.html', [
            'enabled' => true,
            'method' => 'attachment'
        ]);

        $this->assertEmailImageTags([
            'attachment' => 'cid:',
            'base64' => 'data:image/png;base64,',
        ], $message->getBody());
    }

    /**
     * @test
     */
    public function testOverrideTypeAttachment()
    {
        $message = $this->handleBeforeSendPerformedEvent('override-to-attachment.html', [
            'enabled' => true,
            'method' => 'base64'
        ]);

        $this->assertEmailImageTags([
            'attachment' => 'cid:',
            'base64' => 'data:image/png;base64,',
        ], $message->getBody());
    }

    /**
     * @test
     */
    public function testGracefulFailureWithAttachments()
    {
        $message = $this->handleBeforeSendPerformedEvent('graceful-fails.html', [
            'enabled' => true,
            'method' => 'attachment'
        ]);

        $this->assertEmailImageTags([
            'host' => 'http://example.com/test.png',
            'image' => 'http://localhost/other.png',
            'source' => 'whatever',
            'syntax' => 'embed:whatever',
            'class' => 'embed:WrongEntityClassName:1',
            'implementation' => 'embed:Eduardokum\\LaravelMailAutoEmbed\\Tests\\fixtures\\WrongEntity:1',
            'not found' => 'embed:Eduardokum\\LaravelMailAutoEmbed\\Tests\\fixtures\\PictureEntity:9',
        ], $message->getBody());
    }

    /**
     * @test
     */
    public function testGracefulFailureWithBase64()
    {
        $message = $this->handleBeforeSendPerformedEvent('graceful-fails.html', [
            'enabled' => true,
            'method' => 'base64'
        ]);

        $this->assertEmailImageTags([
            'host' => 'http://example.com/test.png',
            'image' => 'http://localhost/other.png',
            'source' => 'whatever',
            'syntax' => 'embed:whatever',
            'class' => 'embed:WrongEntityClassName:1',
            'implementation' => 'embed:Eduardokum\\LaravelMailAutoEmbed\\Tests\\fixtures\\WrongEntity:1',
            'not found' => 'embed:Eduardokum\\LaravelMailAutoEmbed\\Tests\\fixtures\\PictureEntity:9',
        ], $message->getBody());
    }

    /**
     * @test
     */
    public function testDoesntHandleSendPerformedEvent()
    {
        $message = $this->createSwiftMessage('<h1>Test</h1>');

        $embedPlugin = new SwiftEmbedImages(['enabled' => true, 'method' => 'attachment']);

        $this->assertTrue(
            $embedPlugin->sendPerformed($this->createSwiftEvent($message))
        );
    }
}
