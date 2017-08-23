<?php
/**
 * Created by PhpStorm.
 * User: chendong
 * Date: 2017/8/21
 * Time: 19:38
 */

use PHPUnit\Framework\TestCase;
use cdcchen\psr7\Cookie;

final class CookieTest extends TestCase
{
    public function testCanCreateDefaultArguments()
    {
        $this->assertInstanceOf(Cookie::class, new Cookie('cookie_name'));
    }

    public function testConstructArgumentNameIsRight()
    {
        $name = 'cookie_name';
        $value = 'cookie_value';
        $expires = 1000;
        $path = '/api';
        $domain = '.baidu.com';
        $secure = false;
        $httpOnly = true;

        $cookie = new Cookie($name, $value, $expires, $path, $domain, $secure, $httpOnly);
        $this->assertEquals($name, $cookie->name);
        $this->assertEquals($value, $cookie->value);
        $this->assertEquals($expires, $cookie->expires);
        $this->assertEquals($path, $cookie->path);
        $this->assertEquals($domain, $cookie->domain);
        $this->assertEquals($secure, $cookie->secure);
        $this->assertEquals($httpOnly, $cookie->httpOnly);
    }

    public function testHaveAllAttributesCookieStringIsOk()
    {
        $name = 'cookie_name';
        $value = 'cookie_value';
        $expires = 1000;
        $path = '/api';
        $domain = '.baidu.com';
        $secure = true;
        $httpOnly = true;

        $expiresText = gmdate(DATE_COOKIE, $expires);
        $cookieLine = "{$name}={$value}; Expires={$expiresText}; Max-Age=1000; Path={$path}; Domain={$domain}; HttpOnly; Secure";

        $cookie = new Cookie($name, $value, $expires, $path, $domain, $secure, $httpOnly);
        $this->assertEquals($cookieLine, (string)$cookie);
    }

    public function testOnlyCookieValueStringIsOk()
    {
        $name = 'cookie_name';
        $value = 'cookie_value';
        $cookieLine = "{$name}={$value}; Path=/";

        $cookie = new Cookie($name, $value);
        $this->assertEquals($cookieLine, (string)$cookie);
    }

    public function testSetCookieExpiresStringIsOk()
    {
        $name = 'cookie_name';
        $value = 'cookie_value';
        $expires = 6232376;

        $expiresText = gmdate(DATE_COOKIE, $expires);
        $cookieLine = "{$name}={$value}; Expires={$expiresText}; Max-Age={$expires}; Path=/";

        $cookie = new Cookie($name, $value, $expires);
        $this->assertEquals($cookieLine, (string)$cookie);
    }

    public function testSetCookiePathStringIsOk()
    {
        $name = 'cookie_name';
        $value = 'cookie_value';
        $path = '/test';
        $cookieLine = "{$name}={$value}; Path={$path}";

        $cookie = new Cookie($name, $value, 0, $path);
        $this->assertEquals($cookieLine, (string)$cookie);
    }

    public function testSetCookieDomainStringIsOk()
    {
        $name = 'cookie_name';
        $value = 'cookie_value';
        $domain = '.baidu.com';
        $cookieLine = "{$name}={$value}; Path=/; Domain={$domain}";

        $cookie = new Cookie($name, $value, 0, '/', $domain);
        $this->assertEquals($cookieLine, (string)$cookie);
    }

    public function testSetCookieHttpOnlyStringIsOk()
    {
        $name = 'cookie_name';
        $value = 'cookie_value';
        $cookieLine = "{$name}={$value}; Path=/; HttpOnly";

        $cookie = new Cookie($name, $value, 0, '/', '', false, true);
        $this->assertEquals($cookieLine, (string)$cookie);
    }

    public function testSetCookieSecureStringIsOk()
    {
        $name = 'cookie_name';
        $value = 'cookie_value';
        $cookieLine = "{$name}={$value}; Path=/; Secure";

        $cookie = new Cookie($name, $value, 0, '/', '', true);
        $this->assertEquals($cookieLine, (string)$cookie);
    }
}