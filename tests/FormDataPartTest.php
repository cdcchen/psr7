<?php
/**
 * Created by PhpStorm.
 * User: chendong
 * Date: 2017/10/20
 * Time: 21:00
 */

use cdcchen\psr7\FormDataPart;
use cdcchen\psr7\StreamHelper;
use PHPUnit\Framework\TestCase;

class FormDataPartTest extends TestCase
{
    public function testInstanceSuccess()
    {
        $stream = StreamHelper::createStream(__CLASS__);
        $part = new FormDataPart('inputName', $stream);

        $this->assertInstanceOf(FormDataPart::class, $part);
    }

    /**
     * @dataProvider invalidNames
     */
    public function testInstanceThrowInvalidArgumentExceptionByName()
    {
        $this->expectException(InvalidArgumentException::class);
        $stream = StreamHelper::createStream(__CLASS__);
        new FormDataPart('', $stream);
    }

    public function invalidNames()
    {
        return [
            [''],
            [0],
            [false],
            [null],
            ['0'],
            [1000],
            [new DateTime()],
        ];
    }

    public function testInstanceThrowInvalidArgumentExceptionByHeaders()
    {
        $this->expectException(InvalidArgumentException::class);
        $stream = StreamHelper::createStream(__CLASS__);
        new FormDataPart('inputName', $stream, 'asdfasdf');
    }

    public function test__toString()
    {
        $content = __METHOD__;
        $length = strlen($content);
        $stream = StreamHelper::createStream($content);
        $part = new FormDataPart('inputName', $stream);
        $expected = "Content-Disposition: form-data; name=\"inputName\"\r\nContent-Length: {$length}\r\n\r\n{$content}";

        $this->assertEquals($expected, (string)$part);
    }
}