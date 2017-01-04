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
     * @param $name
     * @param $value
     */
    public function set($name, $value)
    {
        $normalized = strtolower($name);
        $this->headerNames[$normalized] = $name;
        $this->headers[$name] = static::filterHeaderValues((array)$value);
    }

    /**
     * @param $name
     * @param $value
     */
    public function unshift($name, $value)
    {
        $normalized = strtolower($name);
        $this->headerNames[$normalized] = $name;
        $this->headers = [$name => static::filterHeaderValues((array)$value)] + $this->headers;
    }

    /**
     * @param $name
     * @param $value
     */
    public function add($name, $value)
    {
        $normalized = strtolower($name);
        $headerName = $this->headerNames[$normalized];
        $value = static::filterHeaderValues((array)$value);

        if ($this->has($name)) {
            $this->headers[$headerName] = array_merge($this->headers[$headerName], $value);
        } else {
            $this->headers[$headerName] = $value;
        }
    }

    /**
     * @param $name
     * @return mixed|null
     */
    public function get($name)
    {
        $normalized = strtolower($name);
        $headerName = $this->headerNames[$normalized];

        return isset($this->headers[$headerName]) ? $this->headers[$headerName] : null;
    }

    /**
     * @param $name
     * @return bool
     */
    public function has($name)
    {
        return isset($this->headerNames[strtolower($name)]);
    }

    /**
     * @param string $name
     */
    public function remove($name)
    {
        $normalized = strtolower($name);
        $headerName = $this->headerNames[$normalized];
        unset($this->headers[$headerName], $this->headerNames[$normalized]);
    }

    /**
     * Remove all cookies
     */
    public function removeAll()
    {
        $this->headers = $this->headerNames = [];
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
        if ($this->has('host')) {
            return $this->get('Host')[0];
        } else {
            return '';
        }
    }

    /**
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
     * @return string
     */
    public function __toString()
    {
        $header = '';
        foreach ($this->headers as $name => $value) {
            if (strtolower($name) === 'set-cookie') {
                $header .= array_reduce($value, function ($carry, $item) use ($name) {
                    $carry .= Message::HEADER_LINE_EOF . $name . ': ' . $item;
                    return $carry;
                });
            } else {
                $header .= Message::HEADER_LINE_EOF . $name . ': ' . implode(', ', $value);
            }
        }

        return $header;
    }

    /**
     * @param array $values
     * @return array
     */
    private function filterHeaderValues(array $values)
    {
        array_walk($values, function (&$value) {
            $value = trim($value, " \t");
        });
        return $values;
    }
}