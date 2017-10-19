<?php
/**
 * Created by PhpStorm.
 * User: chendong
 * Date: 2017/10/19
 * Time: 17:57
 */

namespace cdcchen\psr7;


use Psr\Http\Message\StreamInterface;

/**
 * Class CompositeStream
 * @package cdcchen\psr7
 */
class CompositeStream implements StreamInterface
{
    /**
     * @var StreamInterface[]
     */
    protected $streams = [];
    /**
     * @var bool
     */
    protected $seekable = true;
    /**
     * @var int
     */
    protected $position = 0;
    /**
     * @var int
     */
    protected $current = 0;

    /**
     * CompositeStream constructor.
     * @param array $streams
     */
    public function __construct(array $streams = [])
    {
        foreach ($streams as $stream) {
            $this->add($stream);
        }
    }

    /**
     * @param StreamInterface $stream
     */
    public function add(StreamInterface $stream)
    {
        if (!$stream->isReadable()) {
            throw new \InvalidArgumentException('The stream must be readable.');
        }

        if (!$stream->isSeekable()) {
            $this->seekable = false;
        }

        $this->streams[] = $stream;
    }

    /**
     * @return int
     */
    public function getCount()
    {
        return count($this->streams);
    }

    /**
     * @return string
     */
    public function __toString()
    {
        try {
            $offset = $this->tell();
            $this->rewind();
            $content = $this->getContents();
            if ($this->seekable) {
                $this->seek($offset);
            }
            return $content;
        } catch (\Exception $e) {
            return '';
        }
    }

    /**
     *
     */
    public function close()
    {
        $this->position = $this->current = 0;
        $this->seekable = true;
        foreach ($this->streams as $stream) {
            $stream->close();
        }
        $this->streams = [];
    }

    /**
     *
     */
    public function detach()
    {
        $this->position = $this->current = 0;
        $this->seekable = true;
        foreach ($this->streams as $stream) {
            $stream->detach();
        }
        $this->streams = [];
    }

    /**
     * @return int|null
     */
    public function getSize()
    {
        $allSize = 0;
        foreach ($this->streams as $stream) {
            if (($size = $stream->getSize()) === null) {
                return null;
            }
            $allSize += $size;
        }
        return $allSize;
    }

    /**
     * @return int
     */
    public function tell()
    {
        return $this->position;
    }

    /**
     * @return bool
     */
    public function eof()
    {
        return empty($this->streams) ||
            ($this->current === count($this->streams) - 1 && $this->streams[$this->current]->eof());
    }

    /**
     * @return bool
     */
    public function isSeekable()
    {
        return $this->seekable;
    }

    /**
     * @param int $offset
     * @param int $whence
     */
    public function seek($offset, $whence = SEEK_SET)
    {
        if (!$this->seekable) {
            throw new \RuntimeException('This CompositeStream is not seekable');
        } elseif ($whence !== SEEK_SET) {
            throw new \RuntimeException('The CompositeStream can only seek with SEEK_SET');
        }

        $this->current = $this->position = 0;

        // Rewind each stream
        foreach ($this->streams as $i => $stream) {
            try {
                $stream->rewind();
            } catch (\Exception $e) {
                throw new \RuntimeException("Unable to seek stream {$i} of the AppendStream", 0, $e);
            }
        }
        // Seek to the actual position by reading from each stream
        while ($this->position < $offset && !$this->eof()) {
            $result = $this->read(min(8096, $offset - $this->position));
            if ($result === '' || $result === null || $result === false) {
                break;
            }
        }
    }

    /**
     *
     */
    public function rewind()
    {
        $this->seek(0);
    }

    /**
     * @return bool
     */
    public function isWritable()
    {
        return false;
    }

    /**
     * @inheritdoc
     */
    public function write($string)
    {
        throw new \RuntimeException('CompositeStream is not allowed to be write.');
    }

    /**
     * @return bool
     */
    public function isReadable()
    {
        return true;
    }

    /**
     * @param int $length
     * @return string
     */
    public function read($length)
    {
        $buffer = '';
        $remaining = $length;
        $goNext = false;
        while ($remaining > 0) {
            if ($goNext || $this->streams[$this->current]->eof()) {
                $goNext = false;
                if ($this->current === count($this->streams) - 1) {
                    break;
                }
                $this->current++;
            }
            $content = $this->streams[$this->current]->read($remaining);
            if ($content === '' || $content === null || $content === false) {
                $goNext = true;
                continue;
            }
            $buffer .= $content;
            $remaining = $length - strlen($buffer);
        }
        $this->position += strlen($buffer);
        return $buffer;
    }

    /**
     * @return string
     */
    public function getContents()
    {
        $buffer = '';
        while (!$this->eof()) {
            if (($buf = $this->read(1048576)) !== '') {
                $buffer .= $buf;
            }
        }
        return $buffer;
    }

    /**
     * @param null $key
     * @return array|null
     */
    public function getMetadata($key = null)
    {
        return $key ? null : [];
    }
}