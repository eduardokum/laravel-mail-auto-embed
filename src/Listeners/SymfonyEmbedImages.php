<?php

namespace Eduardokum\LaravelMailAutoEmbed\Listeners;

use DOMDocument;
use DOMElement;
use Eduardokum\LaravelMailAutoEmbed\Embedder\AttachmentEmbedder;
use Eduardokum\LaravelMailAutoEmbed\Embedder\Base64Embedder;
use Eduardokum\LaravelMailAutoEmbed\Embedder\Embedder;
use Eduardokum\LaravelMailAutoEmbed\Models\EmbeddableEntity;
use Exception;
use Illuminate\Mail\Events\MessageSending;
use Masterminds\HTML5;
use ReflectionClass;
use Swift_Message;
use Symfony\Component\Mime\Email;

class SymfonyEmbedImages
{
    /**
     * @var  array
     */
    private $config;

    /**
     * @var  Email
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
     * @param  MessageSending  $event
     */
    public function beforeSendPerformed(MessageSending $event)
    {
        $this->handle($event->message);
    }

    /**
     * @param Email $message
     * @return void
     */
    public function handle(Email $message)
    {
        $this->message = $message;
        $this->attachImages();
    }

    /**
     * Attaches images by parsing the HTML document.
     */
    private function attachImages()
    {
        // Get body
        $body = $this->message->getHtmlBody();
        if ($body === null) {
            // Not an HTML message
            return;
        }

        // Parse document
        $parser = new HTML5();
        $document = $parser->loadHTML($body);
        if (! $document) {
            // Cannot read
            return;
        }

        // Add images
        $this->attachImagesToDom($document);

        // Replace body
        $this->message->html($parser->saveHTML($document));
    }

    /**
     * @param DOMDocument $document
     *
     * @return void
     * @throws Exception
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
     *
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
                    ->setSymfonyMessage($this->message);
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

            if (! class_exists($className)) {
                return $src;
            }

            $class = new ReflectionClass($className);
            if (! $class->implementsInterface(EmbeddableEntity::class)) {
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

        // Base64 embedding
        if (preg_match('/^data:image\/[a-z]+;base64,/', $src)) {
            return $embedder->fromBase64($src);
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

