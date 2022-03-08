<?php

namespace Eduardokum\LaravelMailAutoEmbed\Embedder;

use Eduardokum\LaravelMailAutoEmbed\Models\EmbeddableEntity;
use Exception;
use Illuminate\Support\Str;
use Swift_EmbeddedFile;
use Swift_Message;
use Symfony\Component\Mime\Email;

class AttachmentEmbedder extends Embedder
{
    /**
     * @var  Swift_Message
     */
    protected $swiftMessage;

    /**
     * @var  Email
     */
    protected $symfonyMessage;

    /**
     * @param Swift_Message $message
     *
     * @return AttachmentEmbedder
     * @throws Exception
     */
    public function setSwiftMessage(Swift_Message $message)
    {
        if ($this->isLaravel9()) {
            throw new Exception('Laravel 9 and greater must use symfony mailer');
        }
        $this->swiftMessage = $message;
        return $this;
    }

    /**
     * @param Email $message
     *
     * @return AttachmentEmbedder
     * @throws Exception
     */
    public function setSymfonyMessage(Email $message)
    {
        if (!$this->isLaravel9()) {
            throw new Exception('Laravel 8 and below must use swift mailer');
        }
        $this->symfonyMessage = $message;
        return $this;
    }

    /**
     * @param string $url
     *
     * @throws Exception
     */
    public function fromUrl($url)
    {
        $localFile = str_replace(url('/'), public_path(), $url);

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
     * @throws Exception
     */
    public function fromPath($path)
    {
        return $this->embed(file_get_contents($path), basename($path), mime_content_type($path));
    }

    /**
     * @param EmbeddableEntity $entity
     *
     * @return string
     * @throws Exception
     */
    public function fromEntity(EmbeddableEntity $entity)
    {
        return $this->embed($entity->getRawContent(), $entity->getFileName(), $entity->getMimeType());
    }

    /**
     * @param string $url
     *
     * @throws Exception
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
                $pathInfo = pathinfo($url);

                $queryStr = parse_url($url, PHP_URL_QUERY);
                parse_str($queryStr, $queryParams);
                $basename = $queryParams['basename'] ?? $pathInfo['basename'];

                return $this->embed($raw, $basename, $contentType);
            }
        }
        return $url;
    }

    /**
     * @param $body
     * @param $name
     * @param $type
     *
     * @return string
     * @throws Exception
     */
    protected function embed($body, $name, $type)
    {
        if ($this->isLaravel9() && !empty($this->symfonyMessage)) {
            $name = Str::random();
            $this->symfonyMessage->embed($body, $name, $type);
            return "cid:$name";
        }

        if (!$this->isLaravel9() && !empty($this->swiftMessage)) {
            return $this->swiftMessage->embed(
                new Swift_EmbeddedFile(
                    $body,
                    $name,
                    $type
                )
            );
        }

        throw new Exception('No message defined');
    }

    /**
     * @return bool
     */
    private function isLaravel9()
    {
        return version_compare(app()->version(), '9.0.0', '>=');
    }
}
