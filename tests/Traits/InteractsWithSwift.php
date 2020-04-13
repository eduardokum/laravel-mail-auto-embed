<?php

namespace Eduardokum\LaravelMailAutoEmbed\Tests\Traits;

use Eduardokum\LaravelMailAutoEmbed\Listeners\SwiftEmbedImages;

/**
 * Shared code for creating messages and events
 */
trait InteractsWithSwift
{
    /**
     * @param  string  $htmlMessage
     * @return \Swift_Message
     */
    protected function createSwiftMessage($htmlMessage)
    {
        $message = new \Swift_Message('test', $htmlMessage);

        return $message;
    }

    /**
     * @param  \Swift_Message  $message
     * @return \Swift_Events_SendEvent
     */
    protected function createSwiftEvent(\Swift_Message $message)
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
    protected function handleBeforeSendPerformedEvent($libraryFile, $options)
    {
        $htmlMessage = $this->getLibraryFile($libraryFile);
        $message = $this->createSwiftMessage($htmlMessage);

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
                $body
            );
        }
    }
}
