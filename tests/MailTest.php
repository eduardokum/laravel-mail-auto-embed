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
     * @param  string  $htmlMessage
     * @param  array   $options
     * @return \Swift_Message
     */
    private function handleBeforeSendPerformedEvent($htmlMessage, $options)
    {
        $message = $this->createSwiftMessage($htmlMessage);

        $embedPlugin = new SwiftEmbedImages($options);
        $embedPlugin->beforeSendPerformed($this->createSwiftEvent($message));

        return $message;
    }

    /**
     * @test
     */
    public function images_are_automatically_embedded_when_enabled()
    {
        $htmlMessage = <<<HTML
<!-- url    --><img src="http://localhost/test.png" />
<!-- entity --><img src="embed:Eduardokum\\LaravelMailAutoEmbed\\Tests\\fixtures\\PictureEntity:1" />
HTML;

        $message = $this->handleBeforeSendPerformedEvent($htmlMessage, ['enabled' => true, 'method' => 'attachment']);

        $this->assertContains('<!-- url    --><img src="cid:',    $message->getBody());
        $this->assertContains('<!-- entity --><img src="cid:', $message->getBody());
    }

    /**
     * @test
     */
    public function image_embed_can_be_skipped()
    {
        $htmlMessage = <<<HTML
<!-- embed --><img src="http://localhost/test.png" />
<!-- skip  --><img src="http://localhost/test.png" data-skip-embed />
HTML;

        $message = $this->handleBeforeSendPerformedEvent($htmlMessage, ['enabled' => true, 'method' => 'attachment']);

        $this->assertContains('<!-- embed --><img src="cid:',                      $message->getBody());
        $this->assertContains('<!-- skip  --><img src="http://localhost/test.png', $message->getBody());
    }

    /**
     * @test
     */
    public function images_are_manually_embedded_when_disabled()
    {
        $htmlMessage = <<<HTML
<!-- ignore --><img src="http://localhost/test.png" />
<!-- embed  --><img src="http://localhost/test.png" data-auto-embed />
HTML;

        $message = $this->handleBeforeSendPerformedEvent($htmlMessage, ['enabled' => false, 'method' => 'attachment']);

        $this->assertContains('<!-- ignore --><img src="http://localhost/test.png', $message->getBody());
        $this->assertContains('<!-- embed  --><img src="cid:',                      $message->getBody());
    }

    /**
     * @test
     */
    public function embed_type_can_be_overriden_to_base64()
    {
        $htmlMessage = <<<HTML
<!-- attachment --><img src="http://localhost/test.png" />
<!-- base64     --><img src="http://localhost/test.png" data-auto-embed="base64" />
HTML;

        $message = $this->handleBeforeSendPerformedEvent($htmlMessage, ['enabled' => true, 'method' => 'attachment']);

        $this->assertContains('<!-- attachment --><img src="cid:',                   $message->getBody());
        $this->assertContains('<!-- base64     --><img src="data:image/png;base64,', $message->getBody());
    }

    /**
     * @test
     */
    public function embed_type_can_be_overriden_to_attachment()
    {
        $htmlMessage = <<<HTML
<!-- attachment --><img src="http://localhost/test.png" data-auto-embed="attachment" />
<!-- base64     --><img src="http://localhost/test.png" />
HTML;

        $message = $this->handleBeforeSendPerformedEvent($htmlMessage, ['enabled' => true, 'method' => 'base64']);

        $this->assertContains('<!-- attachment --><img src="cid:',                   $message->getBody());
        $this->assertContains('<!-- base64     --><img src="data:image/png;base64,', $message->getBody());
    }

    /**
     * @test
     */
    public function embed_fails_gracefully_with_attachments()
    {
        $htmlMessage = <<<HTML
<!-- host           --><img src="http://example.com/test.png" />
<!-- image          --><img src="http://localhost/other.png" />
<!-- source         --><img src="whatever" />
<!-- syntax         --><img src="embed:whatever" />
<!-- class          --><img src="embed:WrongEntityClassName:1" />
<!-- implementation --><img src="embed:Eduardokum\\LaravelMailAutoEmbed\\Tests\\fixtures\\WrongEntity:1" />
<!-- not found      --><img src="embed:Eduardokum\\LaravelMailAutoEmbed\\Tests\\fixtures\\PictureEntity:9" />
HTML;

        $message = $this->handleBeforeSendPerformedEvent($htmlMessage, ['enabled' => true, 'method' => 'attachment']);

        $this->assertContains('<!-- host           --><img src="http://example.com/test.png',                                              $message->getBody());
        $this->assertContains('<!-- image          --><img src="http://localhost/other.png',                                               $message->getBody());
        $this->assertContains('<!-- source         --><img src="whatever',                                                                 $message->getBody());
        $this->assertContains('<!-- syntax         --><img src="embed:whatever',                                                           $message->getBody());
        $this->assertContains('<!-- class          --><img src="embed:WrongEntityClassName:1',                                             $message->getBody());
        $this->assertContains('<!-- implementation --><img src="embed:Eduardokum\\LaravelMailAutoEmbed\\Tests\\fixtures\\WrongEntity:1',   $message->getBody());
        $this->assertContains('<!-- not found      --><img src="embed:Eduardokum\\LaravelMailAutoEmbed\\Tests\\fixtures\\PictureEntity:9', $message->getBody());
    }

    /**
     * @test
     */
    public function embed_fails_gracefully_with_base64()
    {
        $htmlMessage = <<<HTML
<!-- host           --><img src="http://example.com/test.png" />
<!-- image          --><img src="http://localhost/other.png" />
<!-- source         --><img src="whatever" />
<!-- syntax         --><img src="embed:whatever" />
<!-- class          --><img src="embed:WrongEntityClassName:1" />
<!-- implementation --><img src="embed:Eduardokum\\LaravelMailAutoEmbed\\Tests\\fixtures\\WrongEntity:1" />
<!-- not found      --><img src="embed:Eduardokum\\LaravelMailAutoEmbed\\Tests\\fixtures\\PictureEntity:9" />
HTML;

        $message = $this->handleBeforeSendPerformedEvent($htmlMessage, ['enabled' => true, 'method' => 'base64']);

        $this->assertContains('<!-- host           --><img src="http://example.com/test.png',                                              $message->getBody());
        $this->assertContains('<!-- image          --><img src="http://localhost/other.png',                                               $message->getBody());
        $this->assertContains('<!-- source         --><img src="whatever',                                                                 $message->getBody());
        $this->assertContains('<!-- syntax         --><img src="embed:whatever',                                                           $message->getBody());
        $this->assertContains('<!-- class          --><img src="embed:WrongEntityClassName:1',                                             $message->getBody());
        $this->assertContains('<!-- implementation --><img src="embed:Eduardokum\\LaravelMailAutoEmbed\\Tests\\fixtures\\WrongEntity:1',   $message->getBody());
        $this->assertContains('<!-- not found      --><img src="embed:Eduardokum\\LaravelMailAutoEmbed\\Tests\\fixtures\\PictureEntity:9', $message->getBody());
    }

    /**
     * @test
     */
    public function doesnt_handle_sendPerformed_event()
    {
        $message = $this->createSwiftMessage('<h1>Test</h1>');

        $embedPlugin = new SwiftEmbedImages(['enabled' => true, 'method' => 'attachment']);

        $this->assertTrue(
            $embedPlugin->sendPerformed($this->createSwiftEvent($message))
        );
    }
}
