<?php
/**
 * Created by PhpStorm.
 * User: chendong
 * Date: 16/7/28
 * Time: 10:11
 */

namespace cdcchen\psr7;


class HeaderParser
{
    /**
     * @param array|string $lines
     * @return HeaderCollection
     */
    public static function parse($lines)
    {
        if (is_string($lines)) {
            $lines = explode("\r\n", $lines);
        }

        if (!is_array($lines)) {
            throw new \InvalidArgumentException('Header data is not parsed. Argument must be a string or array.');
        }

        $headers = new HeaderCollection;
        foreach ($lines as $line) {
            $header = explode(':', $line, 2);
            array_walk($header, function (&$item, $key) {
                $item = trim($item);
            });

            $name = ucwords($header[0], ' -_');
            $headers->add($name, ($header[1] === '') ? [] : $header[1]);
        }

        return $headers;
    }
}