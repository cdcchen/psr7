<?php
/**
 * Created by PhpStorm.
 * User: chendong
 * Date: 16/7/27
 * Time: 17:25
 */

namespace cdcchen\psr7;


/**
 * Class Cookie
 * @package cdcchen\psr7
 */
class Cookie
{
    /**
     * Cookie constructor.
     * @param string $name
     * @param string $value
     * @param int $expires
     * @param string $path
     * @param string $domain
     * @param bool $secure
     * @param bool $httpOnly
     */
    public function __construct(
        $name,
        $value = '',
        $expires = 0,
        $path = '/',
        $domain = '',
        $secure = false,
        $httpOnly = false
    ) {
        $this->name = (string)$name;
        $this->value = (string)$value;
        $this->expires = (int)$expires;
        $this->path = (string)$path;
        $this->domain = (string)$domain;
        $this->secure = (bool)$secure;
        $this->httpOnly = (bool)$httpOnly;
    }

    /**
     * @var string name of the cookie
     */
    public $name;
    /**
     * @var string value of the cookie
     */
    public $value = '';
    /**
     * @var string domain of the cookie
     */
    public $domain = '';
    /**
     * @var integer the timestamp at which the cookie expires. This is the server timestamp.
     * Defaults to 0, meaning "until the browser is closed".
     */
    public $expires = 0;
    /**
     * @var string the path on the server in which the cookie will be available on. The default is '/'.
     */
    public $path = '/';
    /**
     * @var boolean whether cookie should be sent via secure connection
     */
    public $secure = false;
    /**
     * @var boolean whether the cookie should be accessible only through the HTTP protocol.
     * By setting this property to true, the cookie will not be accessible by scripting languages,
     * such as JavaScript, which can effectively help to reduce identity theft through XSS attacks.
     */
    public $httpOnly = true;


    /**
     * Magic method to turn a cookie object into a string without having to explicitly access [[value]].
     * @return string The value of the cookie. If the value property is null, an empty string will be returned.
     */
    public function __toString()
    {
        $cookie = "{$this->name}={$this->value}";
        if ($this->expires !== 0) {
            $cookie .= '; Expires=' . gmdate(DATE_COOKIE, $this->expires);
            $cookie .= '; Max-Age=' . $this->expires;
        }
        if ($this->path) {
            $cookie .= '; Path=' . $this->path;
        }
        if ($this->domain) {
            $cookie .= '; Domain=' . $this->domain;
        }
        if ($this->httpOnly) {
            $cookie .= '; HttpOnly';
        }
        if ($this->secure) {
            $cookie .= '; Secure';
        }

        return $cookie;
    }
}