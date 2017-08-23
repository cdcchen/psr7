<?php
/**
 * Created by PhpStorm.
 * User: chendong
 * Date: 2017/8/22
 * Time: 14:00
 */

use cdcchen\psr7\Uri;
use PHPUnit\Framework\TestCase;

/**
 * Class UriTest
 * @group psr7
 */
class UriTest extends TestCase
{
    const TEST_URL  = 'http://usr:pss@example.com:81/mypath/myfile.html?a=b%26d&b%5B%5D=2&b[]=3&b[3]=4#myfragment';
    const ERROR_URL = 'http:///%[^:/@?&=#]+%usD';

    #################### test Uri instance methods ############################

    /**
     * @dataProvider getUris
     */
    public function testCreateUriIsOk(Uri $uri)
    {
        $this->assertInstanceOf(Uri::class, $uri);
    }

    public function testThrowInvalidArgumentExceptionWhenCreateByConstruct()
    {
        $this->expectException(\InvalidArgumentException::class);
        new Uri(self::ERROR_URL);
    }

    public function testThrowInvalidArgumentExceptionWhenCreateByString()
    {
        $this->expectException(\InvalidArgumentException::class);
        Uri::createFromString(self::ERROR_URL);
    }

    public function testTriggerErrorWhenPathNoWithSlashAndWithAnAuthority()
    {
        $this->expectException(InvalidArgumentException::class);
        $parts = [
            'host' => 'example.com',
            'path' => 'mypath',
            'user' => 'xiaoming',
            'pass' => '123123',
        ];

        Uri::createFromParts($parts);
    }


    ############################## test Uri attributes ###########################

    /**
     * @dataProvider getUris
     */
    public function testSchemeIsOk(Uri $uri)
    {
        $this->assertEquals('http', $uri->getScheme());
    }

    /**
     * @dataProvider getUris
     */
    public function testHostIsOk(Uri $uri)
    {
        $this->assertEquals('example.com', $uri->getHost());
    }

    /**
     * @dataProvider getUris
     */
    public function testPortIsOk(Uri $uri)
    {
        $this->assertEquals('81', $uri->getPort());
    }

    /**
     * @dataProvider getUris
     */
    public function testPathIsOk(Uri $uri)
    {
        $this->assertEquals('/mypath/myfile.html', $uri->getPath());
    }

    /**
     * @dataProvider getUris
     */
    public function testQueryIsOk(Uri $uri)
    {
        $expect = http_build_query([
            'a' => 'b&d',
            'b' => [2, 3, 3 => 4],
        ], null, '&', PHP_QUERY_RFC3986);
        $this->assertEquals($expect, $uri->getQuery());
    }

    /**
     * @dataProvider getUris
     */
    public function testUserInfoIsOk(Uri $uri)
    {
        $this->assertEquals('usr:pss', $uri->getUserInfo());
    }

    /**
     * @dataProvider getUris
     */
    public function testFragmentIsOk(Uri $uri)
    {
        $this->assertEquals('myfragment', $uri->getFragment());
    }


    ########################### dataProviders ######################################

    public function getValidUrls()
    {
        return [
            ['http://www.baidu.com'],
            ['http://www.baidu.com/search'],
            ['http://www.baidu.com/search?kw'],
            ['http://www.baidu.com/search?kw='],
            ['http://www.baidu.com/search?kw=php'],
            ['http://www.baidu.com/search?kw=php&'],
            ['http://www.baidu.com/search?kw=php&page'],
            ['http://www.baidu.com/search?kw=php&page='],
            ['http://www.baidu.com/search?kw=php&page=20'],
            ['http://www.baidu.com/search?kw=php&page=20#2017'],
        ];
    }

    public function getUris()
    {
        return [
            [new Uri(self::TEST_URL)],
            [Uri::createFromString(self::TEST_URL)],
            [Uri::createFromParts(parse_url(self::TEST_URL))],
        ];
    }
}