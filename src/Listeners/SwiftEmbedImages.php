<?php
namespace Eduardokum\LaravelMailAutoEmbed\Listeners;

use Swift_Events_SendEvent;
use Swift_Events_SendListener;
use Swift_Message;

class SwiftEmbedImages implements Swift_Events_SendListener
{

    /**
     * @var  Swift_Message
     */
    private $message;

    /**
     * @param Swift_Events_SendEvent $evt
     */
    public function beforeSendPerformed(Swift_Events_SendEvent $evt)
    {
        $this->message = $evt->getMessage();
        $this->attachImages();
    }

    public function sendPerformed(Swift_Events_SendEvent $evt)
    {
        return true;
    }

    private function attachImages()
    {
        $html_body = $this->message->getBody();
        $html_body = preg_replace_callback('/<img.*src="(.*?)"\s?(.*)?>/', [$this, 'replaceCallback'], $html_body);
        $this->message->setBody($html_body);
    }

    private function replaceCallback($match) {
        if(filter_var($match[1], FILTER_VALIDATE_URL) === false || strpos($match[0], 'data-skip-embed') !== false) {
            return $match[0];
        }
        return '<img src="' . $this->message->embed(\Swift_Image::fromPath($this->replaceUtlToAbsolute($match[1]))) . '" '.$match[2].'/>';
    }

    private function replaceUtlToAbsolute($file) {
        return str_replace(url('/'), public_path('/'), $file);
    }
}