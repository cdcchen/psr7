<?php
/**
 * Created by PhpStorm.
 * User: chendong
 * Date: 2017/10/19
 * Time: 15:06
 */

namespace cdcchen\psr7;


use Psr\Http\Message\StreamInterface;

class MultipartStream implements StreamInterface
{
    private $boundary;

    public function __construct(array $streams = [], $boundary = null)
    {
        $this->boundary = $boundary ?: md5(uniqid(microtime()), true);
    }
}