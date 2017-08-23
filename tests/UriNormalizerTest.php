<?php
/**
 * Created by PhpStorm.
 * User: chendong
 * Date: 2017/8/23
 * Time: 14:00
 */

use cdcchen\psr7\Uri;
use cdcchen\psr7\UriNormalizer;
use PHPUnit\Framework\TestCase;

class UriNormalizerTest extends TestCase
{
    public function testNormalize_CAPITALIZE_PERCENT_ENCODING()
    {
        $uri = new Uri('http://example.org/a%c2%b1b');
        $normalized = UriNormalizer::normalize($uri, UriNormalizer::CAPITALIZE_PERCENT_ENCODING);
        $expected = 'http://example.org/a%C2%B1b';

        $this->assertEquals($expected, (string)$normalized);
    }

    public function testNormalize_DECODE_UNRESERVED_CHARACTERS()
    {
        $uri = new Uri('http://example.org/%7Eusern%61me/');
        $normalized = UriNormalizer::normalize($uri, UriNormalizer::DECODE_UNRESERVED_CHARACTERS);
        $expected = 'http://example.org/~username/';

        $this->assertEquals($expected, (string)$normalized);
    }

    public function testNormalize_CONVERT_EMPTY_PATH()
    {
        $uri = new Uri('http://example.org');
        $normalized = UriNormalizer::normalize($uri, UriNormalizer::CONVERT_EMPTY_PATH);
        $expected = 'http://example.org/';

        $this->assertEquals($expected, (string)$normalized);
    }

    public function testNormalize_REMOVE_DEFAULT_HOST()
    {
        $uri = new Uri('file://localhost/myfile');
        $normalized = UriNormalizer::normalize($uri, UriNormalizer::REMOVE_DEFAULT_HOST);
        $expected = 'file:///myfile';

        $this->assertEquals($expected, (string)$normalized);
    }

    public function testNormalize_REMOVE_DEFAULT_PORT()
    {
        $uri = new Uri('http://example.org:80/');
        $normalized = UriNormalizer::normalize($uri, UriNormalizer::REMOVE_DEFAULT_PORT);
        $expected = 'http://example.org/';

        $this->assertEquals($expected, (string)$normalized);
    }

    public function testNormalize_REMOVE_DOT_SEGMENTS()
    {
        $uri = new Uri('http://example.org/../a/b/../c/./d.html');
        $normalized = UriNormalizer::normalize($uri, UriNormalizer::REMOVE_DOT_SEGMENTS);
        $expected = 'http://example.org/a/c/d.html';

        $this->assertEquals($expected, (string)$normalized);
    }

    public function testNormalize_REMOVE_DUPLICATE_SLASHES()
    {
        $uri = new Uri('http://example.org//foo///bar.html');
        $normalized = UriNormalizer::normalize($uri, UriNormalizer::REMOVE_DUPLICATE_SLASHES);
        $expected = 'http://example.org/foo/bar.html';

        $this->assertEquals($expected, (string)$normalized);
    }

    public function testNormalize_SORT_QUERY_PARAMETERS()
    {
        $uri = new Uri('?lang=en&article=fred');
        $normalized = UriNormalizer::normalize($uri, UriNormalizer::SORT_QUERY_PARAMETERS);
        $expected = '?article=fred&lang=en';

        $this->assertEquals($expected, (string)$normalized);
    }

    public function testNormalize_PRESERVING_NORMALIZATIONS()
    {
        $uri = new Uri('http://localhost:80/%7Eusern%61me///111/222/../aaa/bbb//cc/../a%c2%b1b?lang=en&article=fred');
        $normalized = UriNormalizer::normalize($uri,
            UriNormalizer::PRESERVING_NORMALIZATIONS | UriNormalizer::REMOVE_DUPLICATE_SLASHES | UriNormalizer::SORT_QUERY_PARAMETERS);
        $expected = 'http://localhost/~username/111/aaa/bbb/a%C2%B1b?article=fred&lang=en';

        $this->assertEquals($expected, (string)$normalized);
    }

    public function testHttpUriIsEquivalent()
    {
        $uri1 = Uri::createFromString('http://localhost:80/%7Eusern%61me///111/222/../aaa/bbb//cc/../a%c2%b1b?lang=en&article=fred');
        $uri2 = Uri::createFromString('http://localhost/~username/111/aaa/bbb/a%C2%B1b?article=fred&lang=en');

        $flags = UriNormalizer::PRESERVING_NORMALIZATIONS | UriNormalizer::REMOVE_DUPLICATE_SLASHES | UriNormalizer::SORT_QUERY_PARAMETERS;
        $this->assertTrue(UriNormalizer::isEquivalent($uri1, $uri2, $flags));
    }

    public function testFileUriIsEquivalent()
    {
        $uri1 = Uri::createFromString('file://localhost/%7Eusern%61me///111/222/../aaa/bbb//cc/../a%c2%b1b?lang=en&article=fred');
        $uri2 = Uri::createFromString('file:///~username/111/aaa/bbb/a%C2%B1b?article=fred&lang=en');

        $flags = UriNormalizer::PRESERVING_NORMALIZATIONS | UriNormalizer::REMOVE_DUPLICATE_SLASHES | UriNormalizer::SORT_QUERY_PARAMETERS;
        $this->assertTrue(UriNormalizer::isEquivalent($uri1, $uri2, $flags));
    }
}