<?php
/**
 * Created by PhpStorm.
 * User: chendong
 * Date: 16/7/28
 * Time: 10:52
 */

namespace cdcchen\psr7;


use ArrayIterator;

/**
 * Class HeaderCollection
 * @package cdcchen\psr7
 */
class HeaderCollection implements \ArrayAccess, \Countable, \IteratorAggregate
{

    /**
     * @var array
     */
    private $headers = [];
    /**
     * @var array
     */
    private $headerNames = [];

    /**
     * Collection constructor.
     * @param array $headers
     */
    public function __construct(array $headers = [])
    {
        foreach ($headers as $name => $value) {
            $this->set($name, $value);
        }
    }

    /**
     * @param string $name
     * @param string|array $value
     */
    public function set($name, $value)
    {
        $normalized = static::normalizeName($name);
        $this->headerNames[$normalized] = $name;
        $this->headers[$name] = static::filterHeaderValues((array)$value);
    }

    /**
     * @param string $name
     * @param string $value
     */
    public function unshift($name, $value)
    {
        $normalized = static::normalizeName($name);
        $this->headerNames[$normalized] = $name;
        $this->headers = [$name => static::filterHeaderValues((array)$value)] + $this->headers;
    }

    /**
     * @param string $name
     * @param string|array $value
     */
    public function add($name, $value)
    {
        $value = static::filterHeaderValues((array)$value);

        if ($this->has($name)) {
            $normalized = static::normalizeName($name);
            $headerName = $this->headerNames[$normalized];
            $this->headers[$headerName] = array_merge($this->headers[$headerName], $value);
        } else {
            $this->set($name, $value);
        }
    }

    /**
     * @param string $name
     * @return array|null
     */
    public function get($name)
    {
        if ($this->has($name)) {
            $normalized = static::normalizeName($name);
            $headerName = $this->headerNames[$normalized];
            return $this->headers[$headerName];
        }

        return null;
    }

    /**
     * @param string $name
     * @return null|string
     */
    public function getLine($name)
    {
        if ($this->has($name)) {
            $normalized = static::normalizeName($name);
            $headerName = $this->headerNames[$normalized];
            return implode(', ', $this->headers[$headerName]);
        }

        return null;
    }

    /**
     * @param string $name
     * @return bool
     */
    public function has($name)
    {
        $normalized = static::normalizeName($name);
        return isset($this->headerNames[$normalized]);
    }

    /**
     * @param string $name
     */
    public function remove($name)
    {
        if ($this->has($name)) {
            $normalized = static::normalizeName($name);
            $headerName = $this->headerNames[$normalized];
            unset($this->headers[$headerName], $this->headerNames[$normalized]);
        }
    }

    /**
     * Remove all cookies
     */
    public function removeAll()
    {
        $this->headers = $this->headerNames = [];
    }

    /**
     * @param Cookie $cookie
     */
    public function addCookie(Cookie $cookie)
    {
        $this->add('Set-Cookie', (string)$cookie);
    }

    /**
     * @param CookieCollection $cookies
     */
    public function addCookies(CookieCollection $cookies)
    {
        $this->add('Set-Cookie', $cookies->getValues());
    }

    /**
     * @return array
     */
    public function all()
    {
        return $this->headers;
    }

    /**
     * @return string
     */
    public function getHost()
    {
        if ($this->has('Host')) {
            return $this->get('Host')[0];
        }

        return '';
    }

    /**
     * Alias of all()
     * @return array
     */
    public function toArray()
    {
        return $this->headers;
    }

    /**
     * @inheritdoc
     */
    public function getIterator()
    {
        return new ArrayIterator($this->headers);
    }

    /**
     * @inheritdoc
     */
    public function offsetExists($offset)
    {
        return $this->has($offset);
    }

    /**
     * @inheritdoc
     */
    public function offsetGet($offset)
    {
        return $this->get($offset);
    }

    /**
     * @inheritdoc
     */
    public function offsetSet($offset, $value)
    {
        $this->set($offset, $value);
    }

    /**
     * @inheritdoc
     */
    public function offsetUnset($offset)
    {
        $this->remove($offset);
    }

    /**
     * @inheritdoc
     */
    public function count()
    {
        return count($this->headers);
    }

    /**
     * @return array
     */
    public function getLines()
    {
        $lines = [];
        foreach ($this->headers as $name => $value) {
            if (static::normalizeName($name) === 'set-cookie') {
                $cookies = array_map(function ($item) use ($name) {
                    return "{$name}: {$item}";
                }, $value);
                $lines = array_merge($lines, $cookies);
            } else {
                $lines[] = $name . ': ' . implode(', ', $value);
            }
        }

        return $lines;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        $headerLines = [];
        foreach ($this->headers as $name => $value) {
            if (static::normalizeName($name) === 'set-cookie') {
                $headerLines[] = array_map(function ($item) use ($name) {
                    return "{$name}: {$item}";
                }, $value);
            } else {
                $headerLines[] = $name . ': ' . implode(', ', $value);
            }
        }

        return join(Message::HEADER_LINE_EOF, $headerLines);
    }

    /**
     * @param array $values
     * @return array
     */
    public
    static function filterHeaderValues(
        array $values
    ) {
        array_walk($values, function (&$value) {
            $value = trim($value, " \t");
        });
        return $values;
    }

    /**
     * @param string $name
     * @return string
     */
    private
    static function normalizeName(
        $name
    ) {
        return strtolower($name);
    }
}