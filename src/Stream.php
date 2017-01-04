<?php
/**
 * Created by PhpStorm.
 * User: chendong
 * Date: 16/7/24
 * Time: 11:50
 */

namespace cdcchen\psr7;


use Exception;
use Psr\Http\Message\StreamInterface;
use RuntimeException;

/**
 * Class Stream
 * @package cdcchen\psr7
 */
class Stream implements StreamInterface
{
    /**
     * @var resource
     */
    private $stream;
    /**
     * @var int|null
     */
    private $size;
    /**
     * @var bool
     */
    private $seekable;
    /**
     * @var bool
     */
    private $readable;
    /**
     * @var bool
     */
    private $writable;
    /**
     * @var array|mixed|null
     */
    private $uri;
    /**
     * @var array|mixed
     */
    private $customMetadata;

    /**
     * @var array Hash of readable and writable stream types
     */
    private static $readWriteHash = [
        'read' => [
            'r' => true,
            'w+' => true,
            'r+' => true,
            'x+' => true,
            'c+' => true,
            'rb' => true,
            'w+b' => true,
            'r+b' => true,
            'x+b' => true,
            'c+b' => true,
            'rt' => true,
            'w+t' => true,
            'r+t' => true,
            'x+t' => true,
            'c+t' => true,
            'a+' => true
        ],
        'write' => [
            'w' => true,
            'w+' => true,
            'rw' => true,
            'r+' => true,
            'x+' => true,
            'c+' => true,
            'wb' => true,
            'w+b' => true,
            'r+b' => true,
            'x+b' => true,
            'c+b' => true,
            'w+t' => true,
            'r+t' => true,
            'x+t' => true,
            'c+t' => true,
            'a' => true,
            'a+' => true
        ]
    ];


    /**
     * Stream constructor.
     * @param resource $stream
     * @param array $options
     */
    public function __construct($stream, $options = [])
    {
        if (!is_resource($stream)) {
            throw new \InvalidArgumentException('Stream must be a resource');
        }

        if (isset($options['size'])) {
            $this->size = $options['size'];
        }
        $this->customMetadata = isset($options['metadata']) ? $options['metadata'] : [];
        $this->stream = $stream;
        $meta = stream_get_meta_data($this->stream);
        $this->uri = $this->getMetadata('uri');

        $this->seekable = $meta['seekable'];
        $this->readable = isset(self::$readWriteHash['read'][$meta['mode']]);
        $this->writable = isset(self::$readWriteHash['write'][$meta['mode']]);
    }

    /**
     * @param string $name
     */
    public function __get($name)
    {
        if ($name === 'stream') {
            throw new RuntimeException('The stream is detached');
        }
        throw new \BadMethodCallException('No value for ' . $name);
    }

    /**
     * Closes the stream when the destructed
     */
    public function __destruct()
    {
        $this->close();
    }


    /**
     * @inheritdoc
     */
    public function __toString()
    {
        try {
            $offset = $this->tell();
            $this->rewind();
            $content = (string)stream_get_contents($this->stream);
            $this->seek($offset);
            return $content;
        } catch (Exception $e) {
            return '';
        }
    }

    /**
     * @inheritdoc
     */
    public function close()
    {
        if (isset($this->stream)) {
            if (is_resource($this->stream)) {
                fclose($this->stream);
            }
            $this->detach();
        }
    }

    /**
     * @inheritdoc
     */
    public function detach()
    {
        if (!isset($this->stream)) {
            return null;
        }

        $result = $this->stream;
        unset($this->stream);
        $this->size = $this->uri = null;
        $this->readable = $this->writable = $this->seekable = false;
        return $result;
    }

    /**
     * @inheritdoc
     */
    public function getSize()
    {
        if ($this->size !== null) {
            return $this->size;
        }

        if (!isset($this->stream)) {
            return null;
        }

        // Clear the stat cache if the stream has a URI
        if ($this->uri) {
            clearstatcache(true, $this->uri);
        }

        $stats = fstat($this->stream);
        if (isset($stats['size'])) {
            $this->size = $stats['size'];
            return $this->size;
        }

        return null;
    }

    /**
     * @inheritdoc
     */
    public function tell()
    {
        $result = ftell($this->stream);
        if ($result === false) {
            throw new \RuntimeException('Unable to determine stream position');
        }

        return $result;
    }

    /**
     * @inheritdoc
     */
    public function eof()
    {
        return !$this->stream || feof($this->stream);
    }

    /**
     * @inheritdoc
     */
    public function isSeekable()
    {
        return $this->seekable;
    }

    /**
     * @inheritdoc
     */
    public function seek($offset, $whence = SEEK_SET)
    {
        if (!$this->seekable) {
            throw new RuntimeException('Stream is not seekable');
        } elseif (fseek($this->stream, $offset, $whence) === -1) {
            $whence = var_export($whence, true);
            throw new RuntimeException("Unable to seek to stream position {$offset} with whence {$whence}");
        }
    }

    /**
     * @inheritdoc
     */
    public function rewind()
    {
        if (!$this->seekable) {
            throw new RuntimeException('Stream is not seekable');
        }

        $this->seek(0);
    }

    /**
     * @inheritdoc
     */
    public function isWritable()
    {
        return $this->writable;
    }

    /**
     * @inheritdoc
     */
    public function write($string)
    {
        if (!$this->writable) {
            throw new RuntimeException('Cannot write to a non-writable stream');
        }

        // We can't know the size after writing anything
        $this->size = null;
        $result = fwrite($this->stream, $string);
        if ($result === false) {
            throw new RuntimeException('Unable to write to stream');
        }

        return $result;
    }

    /**
     * @inheritdoc
     */
    public function isReadable()
    {
        return $this->readable;
    }

    /**
     * @inheritdoc
     */
    public function read($length)
    {
        if (!$this->readable) {
            throw new RuntimeException('Cannot read from non-readable stream');
        }

        if ($length < 0) {
            throw new RuntimeException('Length parameter cannot be negative');
        }
        if ($length === 0) {
            return '';
        }

        if (($string = fread($this->stream, $length)) === false) {
            throw new \RuntimeException('Unable to read from stream');
        }

        return $string;
    }

    /**
     * @inheritdoc
     */
    public function getContents()
    {
        $contents = stream_get_contents($this->stream);
        if ($contents === false) {
            throw new RuntimeException('Unable to read stream contents');
        }
        return $contents;
    }

    /**
     * @inheritdoc
     */
    public function getMetadata($key = null)
    {
        if (!isset($this->stream)) {
            return $key === null ? [] : null;
        }

        if ($key === null) {
            return $this->customMetadata + stream_get_meta_data($this->stream);
        } elseif (isset($this->customMetadata[$key])) {
            return $this->customMetadata[$key];
        }

        $meta = stream_get_meta_data($this->stream);
        return isset($meta[$key]) ? $meta[$key] : null;
    }

}