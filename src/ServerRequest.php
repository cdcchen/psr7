<?php
/**
 * Created by PhpStorm.
 * User: chendong
 * Date: 16/7/24
 * Time: 11:07
 */

namespace cdcchen\psr7;


use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UploadedFileInterface;
use InvalidArgumentException;

/**
 * Class ServerRequest
 * @package cdcchen\psr7
 */
class ServerRequest extends Request implements ServerRequestInterface
{
    /**
     * @var array
     */
    private $attributes = [];
    /**
     * @var array
     */
    private $cookieParams = [];
    /**
     * @var null|array|object
     */
    private $parsedBody;
    /**
     * @var array
     */
    private $queryParams = [];
    /**
     * @var array
     */
    private $serverParams;
    /**
     * @var array
     */
    private $uploadedFiles = [];


    /**
     * ServerRequest constructor.
     * @param string $method
     * @param string $uri
     * @param HeaderCollection|null $headers
     * @param null|Stream $body
     * @param string $version
     * @param array $serverParams
     * @param array $cookieParams
     */
    public function __construct(
        $method,
        $uri,
        HeaderCollection $headers = null,
        $body = null,
        $version = '1.1',
        array $serverParams = [],
        array $cookieParams = []
    ) {
        parent::__construct($method, $uri, $headers, $body, $version);
        $this->serverParams = $serverParams;

        $cookieLine = $headers->getLine('Cookie');
        if ($cookieLine !== null) {
            $this->cookieParams = array_merge(CookieParser::parse($cookieLine), $cookieParams);
        }
    }

    /**
     * @inheritdoc
     */
    public function getServerParams()
    {
        return $this->serverParams;
    }

    /**
     * @inheritdoc
     */
    public function getCookieParams()
    {
        return $this->cookieParams;
    }

    /**
     * @param array $cookies
     * @return static
     */
    public function withCookieParams(array $cookies)
    {
        $new = clone $this;
        $new->cookieParams = $cookies;

        return $new;
    }

    /**
     * @inheritdoc
     */
    public function getQueryParams()
    {
        return $this->queryParams;
    }

    /**
     * @param array $query
     * @return static
     */
    public function withQueryParams(array $query)
    {
        $new = clone $this;
        $new->queryParams = $query;

        return $new;
    }

    /**
     * @inheritdoc
     */
    public function getUploadedFiles()
    {
        return $this->uploadedFiles;
    }

    /**
     * @param array $uploadedFiles
     * @return static
     * @throws InvalidArgumentException
     */
    public function withUploadedFiles(array $uploadedFiles)
    {
        foreach ($uploadedFiles as $file) {
            if (!($file instanceof UploadedFileInterface)) {
                throw new InvalidArgumentException('Invalid instance of UploadedFileInterface');
            }
        }

        $new = clone $this;
        $new->uploadedFiles = $uploadedFiles;

        return $new;
    }

    /**
     * @inheritdoc
     */
    public function getParsedBody()
    {
        return $this->parsedBody;
    }

    /**
     * @param array|null|object $data
     * @return static
     */
    public function withParsedBody($data)
    {
        $new = clone $this;
        $new->parsedBody = $data;

        return $new;
    }

    /**
     * @inheritdoc
     */
    public function getAttributes()
    {
        return $this->attributes;
    }

    /**
     * @inheritdoc
     */
    public function getAttribute($name, $default = null)
    {
        return array_key_exists($name, $this->attributes) ? $this->attributes[$name] : $default;
    }

    /**
     * @param string $name
     * @param mixed $value
     * @return static
     */
    public function withAttribute($name, $value)
    {
        $new = clone $this;
        $new->attributes[$name] = $value;

        return $new;
    }

    /**
     * @param string $name
     * @return static
     */
    public function withoutAttribute($name)
    {
        if (!array_key_exists($name, $this->attributes)) {
            return $this;
        }

        $new = clone $this;
        unset($new->attributes[$name]);

        return $new;
    }
}