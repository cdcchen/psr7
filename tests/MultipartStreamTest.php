<?php
/**
 * Created by PhpStorm.
 * User: chendong
 * Date: 2017/10/20
 * Time: 21:43
 */

use cdcchen\psr7\FormDataPart;
use cdcchen\psr7\MultipartStream;
use cdcchen\psr7\StreamHelper;
use PHPUnit\Framework\TestCase;

class MultipartStreamTest extends TestCase
{
    static $boundary = 'asdf0a9sdfjasdifj23f';

    public function testInstance()
    {
        $part1 = new FormDataPart('test_namespace', StreamHelper::createStream(__FILE__));
        $part2 = new FormDataPart('test_class', StreamHelper::createStream(__CLASS__));
        $part3 = new FormDataPart('test_dir', StreamHelper::createStream(__DIR__));

        $stream = new MultipartStream([$part1, $part2, $part3], static::$boundary);

        $this->assertInstanceOf(MultipartStream::class, $stream);

        return $stream;
    }

    /**
     * @param MultipartStream $stream
     * @depends testInstance
     */
    public function testStreamContents(MultipartStream $stream)
    {
        $boundary = static::$boundary;

        $expected = "--{$boundary}\r\n";
        $expected .= "Content-Disposition: form-data; name=\"test_namespace\"\r\n";
        $length = strlen(__FILE__);
        $expected .= "Content-Length: $length\r\n\r\n" . __FILE__ . "\r\n";

        $expected .= "--{$boundary}\r\n";
        $expected .= "Content-Disposition: form-data; name=\"test_class\"\r\n";
        $length = strlen(__CLASS__);
        $expected .= "Content-Length: $length\r\n\r\n" . __CLASS__ . "\r\n";

        $expected .= "--{$boundary}\r\n";
        $expected .= "Content-Disposition: form-data; name=\"test_dir\"\r\n";
        $length = strlen(__DIR__);
        $expected .= "Content-Length: $length\r\n\r\n" . __DIR__ . "\r\n";

        $expected .= "--{$boundary}--\r\n";
        $this->assertEquals($expected, (string)$stream);
    }
}