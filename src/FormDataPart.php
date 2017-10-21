<?php
/**
 * Created by PhpStorm.
 * User: chendong
 * Date: 2017/10/20
 * Time: 19:31
 */

namespace cdcchen\psr7;


use Psr\Http\Message\StreamInterface;

/**
 * Class FormDataPart
 * @package cdcchen\psr7
 */
class FormDataPart
{
    /**
     * @var string
     */
    private $name;
    /**
     * @var StreamInterface
     */
    private $stream;
    /**
     * @var string
     */
    private $filename;
    /**
     * @var HeaderCollection
     */
    private $headers;

    /**
     * FormDataPart constructor.
     * @param string $name
     * @param StreamInterface $stream
     * @param string $filename
     * @param HeaderCollection|array|null $headers
     */
    public function __construct($name, StreamInterface $stream, $filename = null, $headers = null)
    {
        if (empty($name) || !is_string($name)) {
            throw new \InvalidArgumentException('name must be a not empty string.');
        }

        if ($headers instanceof HeaderCollection) {
            $this->headers = $headers;
        } elseif (is_array($headers)) {
            $this->headers = new HeaderCollection($headers);
        } elseif ($headers === null) {
            $this->headers = new HeaderCollection();
        } else {
            throw new \InvalidArgumentException('headers must be a array or an instance of HeaderCollection or null.');
        }

        $this->name = $name;
        $this->stream = $stream;
        $this->filename = $filename;

        $this->handleHeaders();
    }

    /**
     * handle headers
     */
    protected function handleHeaders()
    {
        if (!$this->headers->has('Content-Disposition')) {
            $contentDisposition = "form-data; name=\"{$this->name}\"";
            if (empty($this->filename)) {
                $uri = $this->stream->getMetadata('uri');
                if ($uri && strncasecmp($uri, 'php://', 6) !== 0) {
                    $this->filename = basename($uri);
                }
            }
            if ($this->filename) {
                $contentDisposition .= "; filename=\"{$this->filename}\"";
            }
            $this->headers->set('Content-Disposition', $contentDisposition);
        }

        if (!$this->headers->has('Content-Length')) {
            $this->headers->set('Content-Length', (string)$this->stream->getSize());
        }
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return (string)$this->headers . "\r\n\r\n" . (string)$this->stream;
    }
}