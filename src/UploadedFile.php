<?php
/**
 * Created by PhpStorm.
 * User: chendong
 * Date: 16/7/24
 * Time: 11:35
 */

namespace cdcchen\psr7;


use InvalidArgumentException;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UploadedFileInterface;
use RuntimeException;

class UploadedFile implements UploadedFileInterface
{
    /**
     * @var int[]
     */
    private static $errors = [
        UPLOAD_ERR_OK,
        UPLOAD_ERR_INI_SIZE,
        UPLOAD_ERR_FORM_SIZE,
        UPLOAD_ERR_PARTIAL,
        UPLOAD_ERR_NO_FILE,
        UPLOAD_ERR_NO_TMP_DIR,
        UPLOAD_ERR_CANT_WRITE,
        UPLOAD_ERR_EXTENSION,
    ];

    /**
     * @var string
     */
    private $clientFilename;
    /**
     * @var string
     */
    private $clientMediaType;
    /**
     * @var int
     */
    private $error;
    /**
     * @var null|string
     */
    private $file;
    /**
     * @var bool
     */
    private $moved = false;
    /**
     * @var int
     */
    private $size;
    /**
     * @var StreamInterface
     */
    private $stream;


    /**
     * UploadedFile constructor.
     * @param $stream
     * @param $size
     * @param $errorStatus
     * @param null $clientFilename
     * @param null $clientMediaType
     */
    public function __construct($stream, $size, $errorStatus, $clientFilename = null, $clientMediaType = null)
    {
        $this->setSize($size)
             ->setError($errorStatus)
             ->setClientFilename($clientFilename)
             ->setClientMediaType($clientMediaType);

        if ($this->isOk()) {
            $this->setStream($stream);
        }
    }

    /**
     * Depending on the value set file or stream variable
     *
     * @param mixed $stream
     * @return $this
     * @throws InvalidArgumentException
     */
    private function setStream($stream)
    {
        if ($stream instanceof StreamInterface) {
            $this->stream = $stream;
        } elseif (is_resource($stream)) {
            $this->stream = new Stream($stream);
        } elseif (is_string($stream)) {
            $this->file = $stream;
        } else {
            throw new InvalidArgumentException('Invalid stream or file provided for UploadedFile');
        }

        return $this;
    }

    /**
     * @param int $error
     * @return $this
     * @throws InvalidArgumentException
     */
    private function setError($error)
    {
        if (false === is_int($error)) {
            throw new InvalidArgumentException(
                'Upload file error status must be an integer'
            );
        }
        if (false === in_array($error, UploadedFile::$errors)) {
            throw new InvalidArgumentException(
                'Invalid error status for UploadedFile'
            );
        }
        $this->error = $error;

        return $this;
    }

    /**
     * @param int $size
     * @return $this
     * @throws InvalidArgumentException
     */
    private function setSize($size)
    {
        if (false === is_int($size)) {
            throw new InvalidArgumentException(
                'Upload file size must be an integer'
            );
        }
        $this->size = $size;

        return $this;
    }

    /**
     * @param string|null $clientFilename
     * @return $this
     * @throws InvalidArgumentException
     */
    private function setClientFilename($clientFilename)
    {
        if (false === $this->isStringOrNull($clientFilename)) {
            throw new InvalidArgumentException('Upload file client filename must be a string or null');
        }
        $this->clientFilename = $clientFilename;

        return $this;
    }

    /**
     * @param string|null $clientMediaType
     * @return $this
     * @throws InvalidArgumentException
     */
    private function setClientMediaType($clientMediaType)
    {
        if (false === $this->isStringOrNull($clientMediaType)) {
            throw new InvalidArgumentException('Upload file client media type must be a string or null');
        }
        $this->clientMediaType = $clientMediaType;

        return $this;
    }

    /**
     * @param mixed $param
     * @return boolean
     */
    private function isStringOrNull($param)
    {
        return is_string($param) || is_null($param);
    }

    /**
     * @param mixed $param
     * @return boolean
     */
    private function isNotEmptyString($param)
    {
        return is_string($param) && !empty($param);
    }

    /**
     * Return true if there is no upload error
     *
     * @return boolean
     */
    private function isOk()
    {
        return $this->error === UPLOAD_ERR_OK;
    }

    /**
     * @return boolean
     */
    public function isMoved()
    {
        return $this->moved;
    }

    /**
     * @throws RuntimeException if is moved or not ok
     */
    private function validateActive()
    {
        if (false === $this->isOk()) {
            throw new RuntimeException('Cannot retrieve stream due to upload error');
        }
        if ($this->isMoved()) {
            throw new RuntimeException('Cannot retrieve stream after it has already been moved');
        }
    }


    /**
     * @inheritdoc
     */
    public function getStream()
    {
        $this->validateActive();
        if ($this->stream instanceof StreamInterface) {
            return $this->stream;
        } elseif (is_readable($this->file)) {
            return $this->stream = StreamHelper::createStream(fopen($this->file, 'r+'));
        } else {
            throw new RuntimeException("{$this->file} is not exist or unreadable.");
        }
    }

    /**
     * @inheritdoc
     */
    public function moveTo($targetPath)
    {
        $this->validateActive();
        if (!$this->isNotEmptyString($targetPath)) {
            throw new InvalidArgumentException('Invalid path provided for move operation; must be a non-empty string.');
        }

        if ($this->file) {
            $this->moved = (PHP_SAPI === 'cli')
                ? rename($this->file, $targetPath)
                : move_uploaded_file($this->file, $targetPath);
        } else {
            $destStream = StreamHelper::createStream(fopen($targetPath, 'w'));
            StreamHelper::copyStream($this->getStream(), $destStream);
            $this->moved = true;
        }

        if (false === $this->moved) {
            throw new RuntimeException("Uploaded file could not be moved to {$targetPath}");
        }
    }

    /**
     * @inheritdoc
     */
    public function getSize()
    {
        return $this->size;
    }

    /**
     * @inheritdoc
     */
    public function getError()
    {
        return $this->error;
    }

    /**
     * @inheritdoc
     */
    public function getClientFilename()
    {
        return $this->clientFilename;
    }

    /**
     * @inheritdoc
     */
    public function getClientMediaType()
    {
        return $this->clientMediaType;
    }

}