<?php
/**
 * Created by PhpStorm.
 * User: chendong
 * Date: 2017/10/20
 * Time: 11:56
 */

namespace cdcchen\psr7;


use Psr\Http\Message\StreamInterface;

/**
 * Trait StreamDecoratorTrait
 * @package cdcchen\psr7
 */
trait StreamDecoratorTrait
{
    /**
     * @var StreamInterface
     */
    protected $stream;

    /**
     * StreamDecoratorTrait constructor.
     * @param StreamInterface|null $stream
     */
    public function __construct(StreamInterface $stream = null)
    {
        if ($this->stream !== null) {
            $this->stream = $stream;
        }
    }

    /**
     * @return StreamInterface
     */
    protected function getStream()
    {
        if ($this->stream === null) {
            $this->stream = StreamHelper::createStream();
        }

        return $this->stream;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        try {
            return (string)$this->getStream();
        } catch (\Exception $e) {
            // https://bugs.php.net/bug.php?id=53648
            trigger_error('StreamDecorator::__toString exception: ' . (string)$e, E_USER_ERROR);
            return '';
        }
    }

    /**
     * @return bool|string
     */
    public function getContents()
    {
        return $this->getStream()->getContents();
    }

    /**
     * @inheritdoc
     */
    public function close()
    {
        $this->getStream()->close();
    }

    /**
     * @param string|null $key
     * @return array|mixed|null
     */
    public function getMetadata($key = null)
    {
        return $this->getStream()->getMetadata($key);
    }

    /**
     * @return null|resource
     */
    public function detach()
    {
        return $this->getStream()->detach();
    }

    /**
     * @return int|mixed|null
     */
    public function getSize()
    {
        return $this->getStream()->getSize();
    }

    /**
     * @return bool
     */
    public function eof()
    {
        return $this->getStream()->eof();
    }

    /**
     * @return bool|int
     */
    public function tell()
    {
        return $this->getStream()->tell();
    }

    /**
     * @return bool
     */
    public function isReadable()
    {
        return $this->getStream()->isReadable();
    }

    /**
     * @return bool
     */
    public function isWritable()
    {
        return $this->getStream()->isWritable();
    }

    /**
     * @return bool
     */
    public function isSeekable()
    {
        return $this->getStream()->isSeekable();
    }

    /**
     * @inheritdoc
     */
    public function rewind()
    {
        $this->seek(0);
    }

    /**
     * @param $offset
     * @param int $whence
     */
    public function seek($offset, $whence = SEEK_SET)
    {
        $this->getStream()->seek($offset, $whence);
    }

    /**
     * @param $length
     * @return bool|string
     */
    public function read($length)
    {
        return $this->getStream()->read($length);
    }

    /**
     * @param $string
     * @return bool|int
     */
    public function write($string)
    {
        return $this->getStream()->write($string);
    }
}