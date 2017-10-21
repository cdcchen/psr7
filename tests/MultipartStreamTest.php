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

    /**
     * @param MultipartStream $stream
     * @depends testInstance
     */
    public function testStreamFilename(MultipartStream $stream)
    {
        $boundary = static::$boundary;

        $file1 = __DIR__ . '/file1.txt';
        $file2 = __DIR__ . '/file2.txt';
        $fp1 = fopen($file1, 'rb');
        $fp2 = fopen($file2, 'rb');
        $part1 = new FormDataPart('test_file1', StreamHelper::createStream($fp1));
        $part2 = new FormDataPart('test_file2', StreamHelper::createStream($fp2), 'upfile.txt');

        $stream = new MultipartStream([$part1, $part2], static::$boundary);

        $expected = "--{$boundary}\r\n";
        $expected .= "Content-Disposition: form-data; name=\"test_file1\"; filename=\"file1.txt\"\r\n";
        $content1 = file_get_contents($file1);
        $length = strlen($content1);
        $expected .= "Content-Length: $length\r\n\r\n{$content1}\r\n";

        $expected .= "--{$boundary}\r\n";
        $expected .= "Content-Disposition: form-data; name=\"test_file2\"; filename=\"upfile.txt\"\r\n";
        $content2 = file_get_contents($file2);
        $length = strlen($content2);
        $expected .= "Content-Length: $length\r\n\r\n{$content2}\r\n";

        $expected .= "--{$boundary}--\r\n";
        $this->assertEquals($expected, (string)$stream);
    }
}