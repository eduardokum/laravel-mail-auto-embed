<?php

namespace Eduardokum\LaravelMailAutoEmbed\Tests\Traits;

use Eduardokum\LaravelMailAutoEmbed\Listeners\SwiftEmbedImages;
use Illuminate\Mail\Events\MessageSending;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\Message;

/**
 * Shared code for creating messages and events.
 */
trait InteractsWithSymfony
{
    /**
     * @param  string  $htmlMessage
     * @return Email
     */
    protected function createSymfonyMessage($htmlMessage)
    {
        return (new Email())
                ->html($htmlMessage);
    }

    /**
     * @param  string  $libraryFile
     * @param  array   $options
     * @return Email
     */
    protected function handleBeforeSendPerformedEvent($libraryFile, $options)
    {
        $htmlMessage = $this->getLibraryFile($libraryFile);
        $message = $this->createSymfonyMessage($htmlMessage);

        event(new MessageSending($message));

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
