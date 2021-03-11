<?php

namespace Eduardokum\LaravelMailAutoEmbed\Embedder;

use Eduardokum\LaravelMailAutoEmbed\Models\EmbeddableEntity;
use Illuminate\Support\Str;
use Swift_EmbeddedFile;
use Swift_Image;
use Swift_Message;

class AttachmentEmbedder extends Embedder
{
    /**
     * @var Swift_Message
     */
    protected $message;

    /**
     * AttachmentEmbedder constructor.
     *
     * @param Swift_Message $message
     */
    public function __construct(Swift_Message $message)
    {
        $this->message = $message;
    }

    /**
     * @param string $url
     */
    public function fromUrl($url)
    {
        $localFile = str_replace(url('/'), public_path('/'), $url);
        if (file_exists($localFile)) {
            return $this->fromPath($localFile);
        }

        if ($embeddedFromRemoteUrl = $this->fromRemoteUrl($url)) {
            return $embeddedFromRemoteUrl;
        }

        return $url;
    }

    public function fromPath($path)
    {
        if (file_exists($path)) {
            return $this->embed(
                Swift_Image::fromPath($path)
            );
        }

        return $path;
    }

    /**
     * @param EmbeddableEntity $entity
     */
    public function fromEntity(EmbeddableEntity $entity)
    {
        return $this->embed(
            new Swift_EmbeddedFile(
                $entity->getRawContent(),
                $entity->getFileName(),
                $entity->getMimeType()
            )
        );
    }

    /**
     * @param  Swift_EmbeddedFile $attachment
     * @return string
     */
    protected function embed(Swift_EmbeddedFile $attachment)
    {
        return $this->message->embed($attachment);
    }

    /**
     * @param string $url
     */
    public function fromRemoteUrl($url)
    {
        if (filter_var($url, FILTER_VALIDATE_URL)) {
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_BINARYTRANSFER, 1);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            $raw = curl_exec($ch);
            $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
            curl_close($ch);

            if ($httpcode == 200) {
                $pathInfo = pathinfo($url);

                return $this->embed(
                    new Swift_Image($raw, "{$pathInfo['basename']}", $contentType)
                );
            }
        }
    }
}
