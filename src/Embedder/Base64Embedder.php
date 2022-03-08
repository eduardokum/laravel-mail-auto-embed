<?php

namespace Eduardokum\LaravelMailAutoEmbed\Embedder;

use Eduardokum\LaravelMailAutoEmbed\Models\EmbeddableEntity;

class Base64Embedder extends Embedder
{
    /**
     * @param  string  $url
     * @return string
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

    /**
     * @param $path
     *
     * @return string
     */
    public function fromPath($path)
    {
        if (file_exists($path)) {
            return $this->base64String(mime_content_type($path), file_get_contents($path));
        }

        return $path;
    }

    /**
     * @param  EmbeddableEntity  $entity
     * @return string
     */
    public function fromEntity(EmbeddableEntity $entity)
    {
        return $this->base64String($entity->getMimeType(), $entity->getRawContent());
    }

    /**
     * @param  string  $url
     */
    public function fromRemoteUrl($url)
    {
        if (filter_var($url, FILTER_VALIDATE_URL)) {
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            $raw = curl_exec($ch);
            $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
            curl_close($ch);

            if ($httpcode == 200) {
                return $this->base64String($contentType, $raw);
            }
        }

        return $url;
    }

    /**
     * @param  string  $mimeType
     * @param  mixed  $content
     */
    private function base64String($mimeType, $content)
    {
        return 'data:'.$mimeType.';base64,'.base64_encode($content);
    }
}
