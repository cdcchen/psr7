<?php
/**
 * Created by PhpStorm.
 * User: chendong
 * Date: 2017/8/23
 * Time: 16:24
 */

use PHPUnit\Framework\TestCase;
use cdcchen\psr7\Uri;
use cdcchen\psr7\UriResolver;

class UriResolverTest extends TestCase
{
    public function testRemoveDotSegments()
    {
        $path = '/a/b/c/../d/./e/f/.././d';
        $expected = '/a/b/d/e/d';
        $this->assertEquals($expected, UriResolver::removeDotSegments($path));
    }

    public function testResolveWhenRelativeSchemeIsNotEmpty()
    {
        $uri1 = new Uri('http://usr:pss@example.com:81/mypath/myfile.html?a=b%26d&b%5B%5D=2&b[]=3&b[3]=4#myfragment');
        $uri2 = new Uri('http://new_usr:new_pss@new-example.com:8881/a/../new_path/new_file.html?a=new_a#new_fragment');
        $expected = 'http://new_usr:new_pss@new-example.com:8881/new_path/new_file.html?a=new_a#new_fragment';

        $this->assertEquals($expected, (string)UriResolver::resolve($uri1, $uri2));
    }

    public function testResolveWhenRelativeUriIsEmpty()
    {
        $uri1 = new Uri('http://usr:pss@example.com:81/mypath/myfile.html?a=b%26d&b%5B%5D=2&b[]=3&b[3]=4#myfragment');
        $this->assertEquals((string)$uri1, (string)UriResolver::resolve($uri1, new Uri('')));
    }

    public function testResolveWhenRelativeSchemeIsEmpty()
    {
        $uri1 = new Uri('http://usr:pss@example.com:81/mypath/myfile.html?a=b%26d&b%5B%5D=2&b[]=3&b[3]=4#myfragment');
        $uri2 = new Uri('//new_usr:new_pss@new-example.com:8881/new_path/new_file.html?a=new_a#new_fragment');
        $expected = 'http://new_usr:new_pss@new-example.com:8881/new_path/new_file.html?a=new_a#new_fragment';
        $this->assertEquals($expected, (string)UriResolver::resolve($uri1, $uri2));
    }

    public function testRelativizeWhenTargetHasNewPath()
    {
        $base = new Uri('http://example.com/a/b/');
        $target = new Uri('http://example.com/a/b/c');

        $this->assertEquals('c', (string)UriResolver::relativize($base, $target));
    }

    public function testRelativizeShouldReturnSegmentsPath()
    {
        $base = new Uri('http://example.com/a/b/');
        $target = new Uri('http://example.com/a/x/y');

        $this->assertEquals('../x/y', (string)UriResolver::relativize($base, $target));
    }

    public function testRelativizeWhenTargetHasQuery()
    {
        $base = new Uri('http://example.com/a/b/');
        $target = new Uri('http://example.com/a/b/?user=abcdef');

        $this->assertEquals('?user=abcdef', (string)UriResolver::relativize($base, $target));
    }

    public function testRelativizeWhenTargetHasBaseEqualsTarget()
    {
        $base = new Uri('http://example.com/a/b/');
        $target = new Uri('http://example.com/a/b/');

        $this->assertEquals('', (string)UriResolver::relativize($base, $target));
    }

    public function testRelativizeWhenTargetHasNewAuthority()
    {
        $base = new Uri('http://example.com/a/b/');
        $target = new Uri('http://new-example.org/a/b/');
        $expected = '//new-example.org/a/b/';

        $this->assertEquals($expected, (string)UriResolver::relativize($base, $target));
    }

    public function testRelativizeWhenTargetHavsSegment()
    {
        $base = new Uri('http://example.com/a/b/');
        $target = new Uri('http://example.com/a/b/#my-segment');

        $this->assertEquals('#my-segment', (string)UriResolver::relativize($base, $target));
    }
}