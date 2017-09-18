<?php

namespace Eduardokum\LaravelMailAutoEmbed\Listeners;

use Eduardokum\LaravelMailAutoEmbed\Models\EmbeddableEntity;
use ReflectionClass;
use Swift_EmbeddedFile;
use Swift_Events_SendEvent;
use Swift_Events_SendListener;
use Swift_Image;
use Swift_Message;

class SwiftEmbedImages implements Swift_Events_SendListener
{
    /**
     * @var  array
     */
    private $config;

    /**
     * @var  Swift_Message
     */
    private $message;

    /**
     * @param  array  $config
     */
    public function __construct($config)
    {
        $this->config = $config;
    }

    /**
     * @param  Swift_Events_SendEvent  $evt
     */
    public function beforeSendPerformed(Swift_Events_SendEvent $evt)
    {
        $this->message = $evt->getMessage();

        $this->attachImages();
    }

    /**
     * @param  Swift_Events_SendEvent  $evt
     * @return bool
     */
    public function sendPerformed(Swift_Events_SendEvent $evt)
    {
        return true;
    }

    /**
     *
     */
    private function attachImages()
    {
        $html_body = $this->message->getBody();

        $html_body = preg_replace_callback('/<img.*src="(.*?)"\s?(.*)?>/', [$this, 'replaceCallback'], $html_body);

        $this->message->setBody($html_body);
    }

    /**
     * @param  array  $match
     * @return string
     */
    private function replaceCallback($match)
    {
        // Don't embed if 'data-skip-embed' is present
        if (strpos($match[0], 'data-skip-embed') !== false) {
            return $match[0];
        }

        // Don't embed if auto-embed is disabled and 'data-auto-embed' is absent
        if (!$this->config['enabled'] && strpos($match[0], 'data-auto-embed') === false) {
            return $match[0];
        }

        // Entity embedding
        if (strpos($match[1], 'embed:') === 0) {
            return $this->embedEntity($match);
        }

        // URL embedding
        if (filter_var($match[1], FILTER_VALIDATE_URL) !== false) {
            return $this->embedUrlMatch($match);
        }

        return $match[0];
    }

    /**
     * @param  array  $match
     * @return string
     */
    private function embedUrlMatch($match)
    {
        return '<img src="'.$this->message->embed(Swift_Image::fromPath($this->replaceUtlToAbsolute($match[1]))).'" '.$match[2].'/>';
    }

    /**
     * @param  array  $match
     * @return string
     */
    private function embedEntity($match)
    {
        $embedParams = explode(':', $match[1]);
        if (count($embedParams) < 3) {
            return $match[0];
        }

        $className = urldecode($embedParams[1]);
        $id = $embedParams[2];

        if (!class_exists($className)) {
            return $match[0];
        }

        $class = new ReflectionClass($className);
        if (! $class->implementsInterface(EmbeddableEntity::class) ) {
            return $match[0];
        }

        /** @var EmbeddableEntity $className */
        if (! $instance = $className::findEmbeddable($id)) {
            return $match[0];
        }

        $embedded = new Swift_EmbeddedFile(
            $instance->getRawContent(),
            $instance->getFileName(),
            $instance->getMimeType()
        );

        return '<img src="'.$this->message->embed($embedded).'" '.$match[2].'/>';
    }

    /**
     * @param  string  $file
     * @return mixed
     */
    private function replaceUtlToAbsolute($file)
    {
        return str_replace(url('/'), public_path('/'), $file);
    }
}
