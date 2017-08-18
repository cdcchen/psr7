<?php
/**
 * Created by PhpStorm.
 * User: chendong
 * Date: 16/7/28
 * Time: 17:49
 */

namespace cdcchen\psr7;


use ArrayIterator;

/**
 * Class CookieCollection
 * @package cdcchen\psr7
 */
class CookieCollection implements \Countable, \IteratorAggregate
{
    /**
     * @var Cookie[]
     */
    protected $cookies = [];


    /**
     * Collection constructor.
     * @param array $cookies
     */
    public function __construct(array $cookies = [])
    {
        foreach ($cookies as $cookie) {
            $this->add($cookie);
        }
    }

    /**
     * @param Cookie $cookie
     */
    public function add(Cookie $cookie)
    {
        $this->cookies[$cookie->name] = $cookie;
    }

    /**
     * @param string $name
     * @return Cookie|mixed|null
     */
    public function get($name)
    {
        return isset($this->cookies[$name]) ? $this->cookies[$name] : null;
    }

    /**
     * @param string $name
     * @return bool
     */
    public function has($name)
    {
        return isset($this->cookies[$name]);
    }

    /**
     * @param bool $fromBrowser
     * @param string|Cookie $cookie
     */
    public function remove($cookie, $fromBrowser = false)
    {

        if ($cookie instanceof Cookie) {
            $cookie->expires = 1;
            $cookie->value = '';
        } else {
            $cookie = new Cookie([
                'name' => $cookie,
                'expires' => 1,
            ]);
        }
        if ($fromBrowser) {
            $this->cookies[$cookie->name] = $cookie;
        } else {
            unset($this->cookies[$cookie->name]);
        }
    }

    /**
     * Remove all cookies
     */
    public function removeAll()
    {
        $this->cookies = [];
    }

    /**
     * @return array|Cookie[]
     */
    public function all()
    {
        return $this->cookies;
    }

    /**
     * @inheritdoc
     */
    public function getIterator()
    {
        return new ArrayIterator($this->cookies);
    }

    /**
     * @inheritdoc
     */
    public function count()
    {
        return count($this->cookies);
    }

    /**
     * @return array
     */
    public function getValues()
    {
        $cookies = [];
        foreach ($this->cookies as $cookie) {
            $cookies[] = (string)$cookie;
        }

        return $cookies;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return join(', ', $this->getValues());
    }
}