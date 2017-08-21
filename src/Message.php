<?php
/**
 * Created by PhpStorm.
 * User: chendong
 * Date: 16/7/17
 * Time: 12:03
 */

namespace cdcchen\psr7;


use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\StreamInterface;

/**
 * Class Message
 * @package cdcchen\psr7
 */
class Message implements MessageInterface
{
    /**
     * The header and the body of the separator
     */
    const HEADER_EOF = "\r\n\r\n";
    /**
     * Header line eol
     */
    const HEADER_LINE_EOF = "\r\n";

    /**
     * @var HeaderCollection
     */
    protected $headers;
    /**
     * @var string
     */
    protected $protocol = '1.1';
    /**
     * @var StreamInterface
     */
    protected $stream;

    /**
     * @return string
     */
    public function getProtocolVersion()
    {
        return $this->protocol;
    }

    /**
     * @param string $version
     * @return static
     */
    public function withProtocolVersion($version)
    {
        if ($this->protocol === $version) {
            return $this;
        }

        $new = clone $this;
        $new->protocol = $version;
        return $new;
    }

    /**
     * @return array
     */
    public function getHeaders()
    {
        return $this->headers->all();
    }

    /**
     * @param string $name
     * @return bool
     */
    public function hasHeader($name)
    {
        return $this->headers->has($name);
    }

    /**
     * @param string $name
     * @return array|null
     */
    public function getHeader($name)
    {
        return $this->headers->get($name) ?: null;
    }

    /**
     * @param string $name
     * @return string
     */
    public function getHeaderLine($name)
    {
        $header = $this->getHeader($name);
        if ($header && is_array($header)) {
            return implode(', ', $header);
        } else {
            return '';
        }
    }

    /**
     * @param string $name
     * @param string|string[] $value
     * @return static
     */
    public function withHeader($name, $value)
    {
        $new = clone $this;
        if ($new->headers->has($name)) {
            unset($new->headers[$name]);
        }
        $new->headers->set($name, $value);

        return $new;
    }

    /**
     * @param string $name
     * @param string|string[] $value
     * @return static
     */
    public function withAddedHeader($name, $value)
    {
        $new = clone $this;
        if ($new->headers->has($name)) {
            $new->headers->add($name, $value);
        } else {
            $new->headers->set($name, $value);
        }

        return $new;
    }

    /**
     * @param string $name
     * @return static
     */
    public function withoutHeader($name)
    {
        if (!$this->headers->has($name)) {
            return $this;
        }

        $new = clone $this;
        $new->headers->remove($name);

        return $new;

    }

    /**
     * @return StreamInterface
     */
    public function getBody()
    {
        if (!$this->stream) {
            $this->stream = StreamHelper::createStream('');
        }

        return $this->stream;
    }

    /**
     * @param StreamInterface $body
     * @return static
     */
    public function withBody(StreamInterface $body)
    {
        if ($body === $this->stream) {
            return $this;
        }

        $new = clone $this;
        $new->stream = $body;
        return $new;
    }

    /**
     * @param HeaderCollection $headers
     * @return static
     */
    protected function setHeaders(HeaderCollection $headers)
    {
        $this->headers = $headers;
        return $this;
    }

}