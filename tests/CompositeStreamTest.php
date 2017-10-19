<?php
/**
 * Created by PhpStorm.
 * User: chendong
 * Date: 2017/10/19
 * Time: 20:14
 */

use cdcchen\psr7\CompositeStream;
use cdcchen\psr7\Stream;
use PHPUnit\Framework\TestCase;


class CompositeStreamTest extends TestCase
{
    /**
     * @var CompositeStream
     */
    static $stream;

    public function setUp()
    {
        $stream1 = new Stream(fopen(__DIR__ . '/file1.txt', 'r'));
        $stream2 = new Stream(fopen(__DIR__ . '/file2.txt', 'r'));
        $stream3 = new Stream(fopen(__DIR__ . '/file3.txt', 'r'));
        static::$stream = new CompositeStream([$stream1, $stream2, $stream3]);
    }

    public function testInstanced1()
    {
        $this->assertInstanceOf(CompositeStream::class, new CompositeStream());
    }

    public function testInstanced2()
    {
        $stream1 = new Stream(fopen(__DIR__ . '/file1.txt', 'r'));
        $stream2 = new Stream(fopen(__DIR__ . '/file2.txt', 'r'));
        $stream = new CompositeStream([$stream1, $stream2]);
        $this->assertInstanceOf(CompositeStream::class, $stream);

        return $stream;
    }

    /**
     * @param CompositeStream $stream
     * @return CompositeStream
     * @depends testInstanced2
     */
    public function testCount(CompositeStream $stream)
    {
        $this->assertEquals(2, $stream->getCount());
        return $stream;
    }

    public function testGetSize()
    {
        $this->assertEquals(62, static::$stream->getSize());
    }

    /**
     * @param CompositeStream $stream
     * @depends testInstanced2
     * @return CompositeStream
     */
    public function testAddStream(CompositeStream $stream)
    {
        $stream3 = new Stream(fopen(__DIR__ . '/file3.txt', 'r'));
        $stream->add($stream3);
        $this->assertEquals(3, $stream->getCount());
        return $stream;
    }

    public function testRead5Bytes()
    {
        $contents = static::$stream->read(5);
        $this->assertEquals('12345', $contents);
    }

    public function testRead10Bytes()
    {
        $contents = static::$stream->read(10);
        $this->assertEquals('1234567890', $contents);
    }

    public function testRead15Bytes()
    {
        $contents = static::$stream->read(15);
        $this->assertEquals('1234567890abcde', $contents);
    }

    public function testGetContents()
    {
        $contents = '1234567890abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $this->assertEquals($contents, static::$stream->getContents());
    }

    public function test__toString()
    {
        $contents = '1234567890abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $this->assertEquals($contents, (string)static::$stream);
    }

    public function testTell()
    {
        static::$stream->seek(3);
        $this->assertEquals(3, static::$stream->tell());
    }

    public function testIsEof()
    {
        static::$stream->seek(66);
        $this->assertTrue(static::$stream->eof());
    }

    public function testIsNotEof()
    {
        static::$stream->seek(22);
        $this->assertFalse(static::$stream->eof());
    }

    public function testRewind()
    {
        static::$stream->seek(30);
        $pos1 = static::$stream->tell();
        static::$stream->rewind();
        $pos2 = static::$stream->tell();
        $this->assertTrue($pos1 === 30 && $pos2 === 0);
    }

    public function testSeek()
    {
        static::$stream->seek(3);
        $this->assertEquals('4567890abc', static::$stream->read(10));
    }

    public function testWrite()
    {
        $this->expectException(RuntimeException::class);
        static::$stream->write(__FILE__);
    }

    public function testIsWrite()
    {
        $this->assertFalse(static::$stream->isWritable());
    }

    public function testClose()
    {
        static::$stream->close();
        $this->assertEquals(0, $this->getCount());
    }

    public function testDetach()
    {
        static::$stream->detach();
        $this->assertEquals(0, $this->getCount());
    }

    public function testGetMetadataUriIsNull()
    {
        $this->assertNull(static::$stream->getMetadata('uri'));
    }

    public function testGetAllMetadataIsEmptyArray()
    {
        $this->assertEquals([], static::$stream->getMetadata());
    }
}