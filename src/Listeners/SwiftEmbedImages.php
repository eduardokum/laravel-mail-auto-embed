<?php

namespace Eduardokum\LaravelMailAutoEmbed\Listeners;

use DOMDocument;
use DOMElement;
use Eduardokum\LaravelMailAutoEmbed\Embedder\AttachmentEmbedder;
use Eduardokum\LaravelMailAutoEmbed\Embedder\Base64Embedder;
use Eduardokum\LaravelMailAutoEmbed\Embedder\Embedder;
use Eduardokum\LaravelMailAutoEmbed\Models\EmbeddableEntity;
use Exception;
use Masterminds\HTML5;
use ReflectionClass;
use Swift_Events_SendEvent;
use Swift_Events_SendListener;
use Swift_Message;
use Swift_Mime_SimpleMessage;
use Symfony\Component\Mime\Email;

class SwiftEmbedImages implements Swift_Events_SendListener
{
    /**
     * @var  array
     */
    private $config;

    /**
     * @var Email|Swift_Mime_SimpleMessage
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
        $this->handle($evt->getMessage());
    }

    /**
     * @param Swift_Mime_SimpleMessage $message
     * @return void
     */
    public function handle(Swift_Mime_SimpleMessage $message)
    {
        $this->message = $message;
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
     *
     * @return Embedder
     * @throws Exception
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
                return (new AttachmentEmbedder())
                    ->setSwiftMessage($this->message);
            case 'base64':
                return new Base64Embedder($this->config);
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

        $appPath = method_exists(app(), 'path') ? app_path($src) : null;
        $publicPath = app()->bound('path.public') ? public_path($src) : null;
        $storagePath = app()->bound('path.storage') ? storage_path($src) : null;
        $storageAppPath = app()->bound('path.storage') ? storage_path("app/$src") : null;
        if (file_exists($src)) {
            return $embedder->fromPath($src);
        } elseif ($publicPath && file_exists($publicPath)) { // Try to guess where the file is at that priority level
            return $embedder->fromPath($publicPath);
        } elseif ($appPath && file_exists($appPath)) {
            return $embedder->fromPath($appPath);
        } elseif ($storagePath && file_exists($storagePath)) {
            return $embedder->fromPath($storagePath);
        } elseif ($storageAppPath && file_exists($storageAppPath)) {
            return $embedder->fromPath($storageAppPath);
        }

        return $src;
    }
}