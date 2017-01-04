<?php
/**
 * Created by PhpStorm.
 * User: chendong
 * Date: 16/7/23
 * Time: 20:00
 */

namespace cdcchen\psr7;


use Fig\Http\Message\RequestMethodInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\UriInterface;

/**
 * Class Request
 * @package cdcchen\psr7
 */
class Request extends Message implements RequestInterface, RequestMethodInterface
{
    /**
     * @var string
     */
    private $method;
    /**
     * @var Uri
     */
    private $uri;
    /**
     * @var string
     */
    private $requestTarget;


    /**
     * @param string $method
     * @param string $uri
     * @param HeaderCollection $headers
     * @param null|mixed $body
     * @param string $version
     */
    public function __construct($method, $uri, HeaderCollection $headers = null, $body = null, $version = '1.1')
    {
        $this->method = strtoupper($method);
        if (!($uri instanceof UriInterface)) {
            $uri = new Uri($uri);
        }
        $this->uri = $uri;
        $this->setHeaders($headers ?: new HeaderCollection());
        if (!$this->hasHeader('Host')) {
            $this->updateHostFromUri();
        }

        if ($body !== '' && $body !== null) {
            $this->stream = StreamHelper::createStream($body);
        }

        $this->protocol = $version;
    }

    /**
     * @inheritdoc
     */
    public function getRequestTarget()
    {
        if ($this->requestTarget !== null) {
            return $this->requestTarget;
        }

        $target = $this->uri->getPath() ?: '/';
        if ($this->uri->getQuery() != '') {
            $target .= '?' . $this->uri->getQuery();
        }

        return $target;
    }

    /**
     * @param mixed $requestTarget
     * @return static
     */
    public function withRequestTarget($requestTarget)
    {
        if (preg_match('/\s/', $requestTarget)) {
            throw new \InvalidArgumentException('Invalid request target provided; cannot contain whitespace.');
        }

        $new = clone $this;
        $new->requestTarget = $requestTarget;

        return $new;
    }

    /**
     * @inheritdoc
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * @param string $method
     * @return static
     */
    public function withMethod($method)
    {
        $new = clone $this;
        $new->method = strtoupper($method);
        return $new;
    }

    /**
     * @inheritdoc
     */
    public function getUri()
    {
        return $this->uri;
    }

    /**
     * @param UriInterface $uri
     * @param bool $preserveHost
     * @return static
     */
    public function withUri(UriInterface $uri, $preserveHost = false)
    {
        if ($uri === $this->uri) {
            return $this;
        }

        $new = clone $this;
        $new->uri = $uri;
        if (!$preserveHost) {
            $new->updateHostFromUri();
        }

        return $new;
    }

    /**
     * Update Host from Uri
     */
    private function updateHostFromUri()
    {
        $host = $this->uri->getHost();
        if (empty($host)) {
            return;
        }

        if (($port = $this->uri->getPort()) !== null) {
            $host .= ':' . $port;
        }

        $this->headers->unshift('Host', $host);
    }
}