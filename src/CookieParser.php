<?php
/**
 * Created by PhpStorm.
 * User: chendong
 * Date: 16/7/28
 * Time: 10:11
 */

namespace cdcchen\psr7;


use InvalidArgumentException;

/**
 * Class CookieParser
 * @package cdcchen\psr7
 */
class CookieParser
{
    /**
     * @param string $header
     * @return array
     */
    public static function parse($header)
    {
        if (!is_string($header)) {
            throw new InvalidArgumentException('Cannot parse Cookie data. Header value must be a string.');
        }

        return static::parseHeader($header);
    }

    /**
     * @param string $header
     * @return array
     */
    private static function parseHeader($header)
    {
        $header = rtrim($header, "\r\n");
        $pieces = preg_split('/\s*[;,]\s*/', $header);
        $cookies = [];
        foreach ($pieces as $cookie) {
            $cookie = explode('=', $cookie, 2);
            if (isset($cookie[1])) {
                $key = urldecode($cookie[0]);
                $value = urldecode($cookie[1]);
                if (!isset($cookies[$key])) {
                    $cookies[$key] = $value;
                }
            }
        }
        return $cookies;
    }
}