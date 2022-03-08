<?php

namespace Eduardokum\LaravelMailAutoEmbed\Listeners;

use DOMDocument;
use DOMElement;
use Eduardokum\LaravelMailAutoEmbed\Embedder\AttachmentEmbedder;
use Eduardokum\LaravelMailAutoEmbed\Embedder\Base64Embedder;
use Eduardokum\LaravelMailAutoEmbed\Embedder\Embedder;
use Eduardokum\LaravelMailAutoEmbed\Models\EmbeddableEntity;
use Masterminds\HTML5;
use ReflectionClass;
use Swift_Events_SendEvent;
use Swift_Events_SendListener;
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
        // Get body
        $body = $this->message->getBody();

        // Parse document
        $parser = new HTML5();
        $document = $parser->loadHTML($body);
        if (! $document) {
            // Cannot read
            return;
        }

        // Invalid HTML (raw message)
        if ($this->shouldSkipDocument($document)) {
            return;
        }

        // Add images
        $this->attachImagesToDom($document);

        // Replace body
        $this->message->setBody($parser->saveHTML($document));

//        $html_body = $this->message->getBody();
//
        /*        $html_body = preg_replace_callback('/<img.*src="(.*?)"\s?(.*)?>/', [$this, 'replaceCallback'], $html_body);*/
//
//        $this->message->setBody($html_body);
    }

    /**
     * @param  DOMDocument $document
     * @return bool
     */
    private function shouldSkipDocument(DOMDocument $document)
    {
        if ($document->childNodes->count() != 1) {
            return false;
        }

        if ($document->childNodes->item(0)->nodeType == XML_DOCUMENT_TYPE_NODE) {
            return true;
        }

        return false;
    }

    /**
     * @param DOMDocument $document
     */
    private function attachImagesToDom(DOMDocument &$document)
    {
        foreach ($document->getElementsByTagName('img') as $image) {
            \assert($image instanceof DOMElement);

            // Skip if embed is not required
            if ($this->needsEmbed($image)) {
                // Get proper embedder
                $embedder = $this->getEmbedder($image);

                // Update src
                $image->setAttribute('src', $this->embed(
                    $embedder,
                    $image->getAttribute('src')
                ));
            }

            // Remove data properties
            $image->removeAttribute('data-skip-embed');
            $image->removeAttribute('data-auto-embed');
        }
    }

    /**
     * @param DOMElement $imageTag
     * @return bool
     */
    private function needsEmbed(DOMElement $imageTag)
    {
        // Don't embed if 'data-skip-embed' is present
        if ($imageTag->hasAttribute('data-skip-embed')) {
            return false;
        }

        // Don't embed if auto-embed is disabled and 'data-auto-embed' is absent
        if (! $this->config['enabled'] && ! $imageTag->hasAttribute('data-auto-embed')) {
            return false;
        }

        return true;
    }

    /**
     * @param DOMElement $imageTag
     * @return Embedder
     */
    private function getEmbedder(DOMElement $imageTag)
    {
        $method = $imageTag->getAttribute('data-auto-embed');
        if (empty($method)) {
            $method = $this->config['method'];
        }

        switch ($method) {
            case 'attachment':
            default:
                return new AttachmentEmbedder($this->message);
            case 'base64':
                return new Base64Embedder();
        }
    }

    /**
     * @param  Embedder  $embedder
     * @param  string    $src
     * @return string
     */
    private function embed(Embedder $embedder, $src)
    {
        // Entity embedding
        if (strpos($src, 'embed:') === 0) {

            $embedParams = explode(':', $src);
            if (count($embedParams) < 3) {
                return $src;
            }

            $className = urldecode($embedParams[1]);
            $id = $embedParams[2];

            if (!class_exists($className)) {
                return $src;
            }

            $class = new ReflectionClass($className);
            if (! $class->implementsInterface(EmbeddableEntity::class) ) {
                return $src;
            }

            /** @var EmbeddableEntity $className */
            if (! $instance = $className::findEmbeddable($id)) {
                return $src;
            }

            return $embedder->fromEntity($instance);
        }

        // URL embedding
        if (filter_var($src, FILTER_VALIDATE_URL) !== false) {
            return $embedder->fromUrl($src);
        }

        return $src;
    }
}
