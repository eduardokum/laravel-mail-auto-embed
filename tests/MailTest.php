<?php

namespace Eduardokum\LaravelMailAutoEmbed\Tests;

use Eduardokum\LaravelMailAutoEmbed\Listeners\SwiftEmbedImages;

class MailTest extends TestCase
{
    /**
     * @param  string  $htmlMessage
     * @return \Swift_Message
     */
    private function createSwiftMessage($htmlMessage)
    {
        $message = new \Swift_Message('test', $htmlMessage);

        return $message;
    }

    /**
     * @param  \Swift_Message  $message
     * @return \Swift_Events_SendEvent
     */
    private function createSwiftEvent(\Swift_Message $message)
    {
        $dispatcher = new \Swift_Events_SimpleEventDispatcher();
        $transport = new \Swift_Transport_NullTransport($dispatcher);
        $event = new \Swift_Events_SendEvent($transport, $message);

        return $event;
    }

    /**
     * @param  string  $libraryFile
     * @param  array   $options
     * @return \Swift_Message
     */
    private function handleBeforeSendPerformedEvent($libraryFile, $options)
    {
        $htmlMessage = $this->getLibraryFile($libraryFile);
        $message = $this->createSwiftMessage($htmlMessage);

        $embedPlugin = new SwiftEmbedImages($options);
        $embedPlugin->beforeSendPerformed($this->createSwiftEvent($message));

        return $message;
    }

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

    /**
     * Check the body for image tags with the given keys as comment preceding them.
     * @param array $expectations
     * @param string $body
     * @return void
     */
    protected function assertEmailImageTags($expectations, $body)
    {
        foreach ($expectations as $comment => $src) {
            // Gimmick of using a DOMDocument parser
            $src = str_replace('\\', '%5C', \htmlspecialchars($src));

            // Fix for PHPUnit <8.0
            // phpcs:ignore Generic.Files.LineLength.TooLong
            $method = \method_exists($this, 'assertStringContainsString') ? 'assertStringContainsString' : 'assertContains';

            // Check if the string is contained within the string
            $this->$method(
                sprintf('<!-- %s --><img src="%s', $comment, $src),
                $body
            );
        }
    }
}
