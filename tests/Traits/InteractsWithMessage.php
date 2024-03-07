<?php

namespace Eduardokum\LaravelMailAutoEmbed\Tests\Traits;

use Eduardokum\LaravelMailAutoEmbed\Listeners\SwiftEmbedImages;
use Eduardokum\LaravelMailAutoEmbed\Listeners\SymfonyEmbedImages;
use Illuminate\Mail\Events\MessageSending;
use Swift_Message;
use Symfony\Component\Mime\Email;

/**
 * Shared code for creating messages and events.
 */
trait InteractsWithMessage
{
    /**
     * @return bool
     */
    private function isLaravel9()
    {
        return version_compare($this->createApplication()->version(), '9.0.0', '>=');
    }

    /**
     * @param  string  $htmlMessage
     * @param  bool    $isRawMessage
     *
     * @return Email|Swift_Message
     */
    protected function createMessage($htmlMessage, $isRawMessage = false)
    {
        if ($this->isLaravel9()) {
            return (new Email())->to('test@test.com')->from('sender@test.com')->subject('test')
                ->text($htmlMessage)
                ->html($isRawMessage ? null : $htmlMessage);
        } else {
            return new Swift_Message('test', $htmlMessage, $isRawMessage ? 'text/plain' : null);
        }
    }

    /**
     * @param  Swift_Message  $message
     * @return \Swift_Events_SendEvent
     */
    protected function createSwiftEvent(Swift_Message $message)
    {
        $dispatcher = new \Swift_Events_SimpleEventDispatcher();
        $transport = new \Swift_Transport_NullTransport($dispatcher);
        $event = new \Swift_Events_SendEvent($transport, $message);

        return $event;
    }

    /**
     * @param  string  $libraryFile
     * @param  array   $options
     * @param  bool    $isRawMessage
     * @return Swift_Message|Email
     */
    protected function handleBeforeSendPerformedEvent($libraryFile, $options, $isRawMessage = false)
    {
        $htmlMessage = $this->getLibraryFile($libraryFile);
        $message = $this->createMessage($htmlMessage, $isRawMessage);

        if ($this->isLaravel9()) {
            $event = new MessageSending($message);
            (new SymfonyEmbedImages($options))
                ->beforeSendPerformed($event);
            $event->message->getBody();
            return $event->message;
        }

        $embedPlugin = new SwiftEmbedImages($options);
        $embedPlugin->beforeSendPerformed($this->createSwiftEvent($message));

        return $message;
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
            // Fix for PHPUnit <8.0
            // phpcs:ignore Generic.Files.LineLength.TooLong
            $method = \method_exists($this, 'assertStringContainsString') ? 'assertStringContainsString' : 'assertContains';

            // Check if the string is contained within the string
            $this->$method(
                sprintf('<!-- %s --><img src="%s', $comment, $src),
                $this->isLaravel9() ? $body->getHtmlBody() : $body->getBody()
            );
        }
    }
}
