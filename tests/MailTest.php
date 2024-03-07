<?php

namespace Eduardokum\LaravelMailAutoEmbed\Tests;

use Eduardokum\LaravelMailAutoEmbed\Listeners\SwiftEmbedImages;
use Eduardokum\LaravelMailAutoEmbed\Tests\Traits\InteractsWithMessage;

class MailTest extends TestCase
{
    use InteractsWithMessage;

    /**
     * @test
     */
    public function testAutomaticAttachmentConversion()
    {
        $message = $this->handleBeforeSendPerformedEvent('embed-when-enabled.html', [
            'enabled' => true,
            'method' => 'attachment',
        ]);

        $this->assertEmailImageTags([
            'url' => 'cid:',
            'entity' => 'cid:',
        ], $message);
    }

    /**
     * @test
     */
    public function testSkippedConversions()
    {
        $message = $this->handleBeforeSendPerformedEvent('embed-can-skip.html', [
            'enabled' => true,
            'method' => 'attachment',
        ]);

        $this->assertEmailImageTags([
            'embed' => 'cid:',
            'skip' => 'http://localhost/test.png',
        ], $message);
    }

    /**
     * @test
     */
    public function testManualConversions()
    {
        $message = $this->handleBeforeSendPerformedEvent('manual-embed-when-disabled.html', [
            'enabled' => false,
            'method' => 'attachment',
        ]);

        $this->assertEmailImageTags([
            'ignore' => 'http://localhost/test.png',
            'embed' => 'cid:',
        ], $message);
    }

    /**
     * @test
     */
    public function testOverrideTypeBase64()
    {
        $message = $this->handleBeforeSendPerformedEvent('override-to-base64.html', [
            'enabled' => true,
            'method' => 'attachment',
        ]);

        $this->assertEmailImageTags([
            'attachment' => 'cid:',
            'base64' => 'data:image/png;base64,',
        ], $message);
    }

    /**
     * @test
     */
    public function testOverrideTypeAttachment()
    {
        $message = $this->handleBeforeSendPerformedEvent('override-to-attachment.html', [
            'enabled' => true,
            'method' => 'base64',
        ]);

        $this->assertEmailImageTags([
            'attachment' => 'cid:',
            'base64' => 'data:image/png;base64,',
        ], $message);
    }

    /**
     * @test
     */
    public function testGracefulFailureWithAttachments()
    {
        $message = $this->handleBeforeSendPerformedEvent('graceful-fails.html', [
            'enabled' => true,
            'method' => 'attachment',
        ]);

        $this->assertEmailImageTags([
            'host' => 'http://example.com/test.png',
            'image' => 'http://localhost/other.png',
            'source' => 'whatever',
            'syntax' => 'embed:whatever',
            'class' => 'embed:WrongEntityClassName:1',
            'implementation' => 'embed:Eduardokum\\LaravelMailAutoEmbed\\Tests\\fixtures\\WrongEntity:1',
            'not found' => 'embed:Eduardokum\\LaravelMailAutoEmbed\\Tests\\fixtures\\PictureEntity:9',
        ], $message);
    }

    /**
     * @test
     */
    public function testGracefulFailureWithBase64()
    {
        $message = $this->handleBeforeSendPerformedEvent('graceful-fails.html', [
            'enabled' => true,
            'method' => 'base64',
        ]);

        $this->assertEmailImageTags([
            'host' => 'http://example.com/test.png',
            'image' => 'http://localhost/other.png',
            'source' => 'whatever',
            'syntax' => 'embed:whatever',
            'class' => 'embed:WrongEntityClassName:1',
            'implementation' => 'embed:Eduardokum\\LaravelMailAutoEmbed\\Tests\\fixtures\\WrongEntity:1',
            'not found' => 'embed:Eduardokum\\LaravelMailAutoEmbed\\Tests\\fixtures\\PictureEntity:9',
        ], $message);
    }

    /**
     * @test
     */
    public function testDoesntHandleSendPerformedEvent()
    {
        if ($this->isLaravel9()) {
            $this->assertTrue(true);
        } else {
            $message = $this->createMessage('<h1>Test</h1>');

            $embedPlugin = new SwiftEmbedImages(['enabled' => true, 'method' => 'attachment']);

            $this->assertTrue(
                $embedPlugin->sendPerformed($this->createSwiftEvent($message))
            );
        }
    }

    /**
     * @test
     */
    public function testDoesntTransformRawMessages()
    {
        $message = $this->handleBeforeSendPerformedEvent('raw-message.txt', [
            'enabled' => true,
            'method' => 'attachment',
        ]);

        $this->assertEquals(
            $this->getLibraryFile('raw-message.txt'),
            ($this->isLaravel9() ? $message->getTextBody() : $message->getBody())
        );
    }

    /**
     * @test
     */
    public function testDoesNotCreateHtmlBodyForSymfonyRawMessage()
    {
        if (! $this->isLaravel9()) {
            $this->assertTrue(true);

            return;
        }

        $message = $this->handleBeforeSendPerformedEvent(
            'raw-message.txt',
            ['enabled' => true, 'method' => 'attachment'],
            true
        );

        $this->assertNull($message->getHtmlBody());
    }
}
