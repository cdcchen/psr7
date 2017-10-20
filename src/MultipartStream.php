<?php
/**
 * Created by PhpStorm.
 * User: chendong
 * Date: 2017/10/19
 * Time: 15:06
 */

namespace cdcchen\psr7;


use Psr\Http\Message\StreamInterface;

/**
 * Class MultipartStream
 * @package cdcchen\psr7
 */
class MultipartStream implements StreamInterface
{
    use StreamDecoratorTrait;

    /**
     * @var string
     */
    private $boundary;

    /**
     * MultipartStream constructor.
     * @param array $parts
     * @param null $boundary
     */
    public function __construct(array $parts = [], $boundary = null)
    {
        $this->boundary = $boundary ?: md5(uniqid(microtime()), true);

        $this->stream = new CompositeStream();
        foreach ($parts as $part) {
            $this->addPart($part);
        }

        $this->stream->add(StreamHelper::createStream("--{$this->boundary}--\r\n"));
    }

    /**
     * @param FormDataPart $part
     */
    protected function addPart(FormDataPart $part)
    {
        $content = "--{$this->boundary}\r\n" . (string)$part . "\r\n";
        $this->stream->add(StreamHelper::createStream($content));
    }

    /**
     * @return string
     */
    public function getBoundary()
    {
        return $this->boundary;
    }

    /**
     * @return bool
     */
    public function isWritable()
    {
        return false;
    }
}