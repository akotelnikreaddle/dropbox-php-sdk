<?php

declare(strict_types=1);

namespace Kunnu\Dropbox;

use GuzzleHttp\Psr7\Utils;
use GuzzleHttp\Psr7\Stream;
use GuzzleHttp\Psr7\MimeType;
use Kunnu\Dropbox\Exceptions\DropboxClientException;

/**
 * DropboxFile
 */
class DropboxFile
{
    const string MODE_READ = 'r';

    const string MODE_WRITE = 'w';

    /**
     * The maximum bytes to read. Defaults to -1 (read all the remaining buffer).
     */
    protected int $maxLength = -1;

    /**
     * Seek to the specified offset before reading.
     * If this number is negative, no seeking will
     * occur and reading will start from the current.
     */
    protected int $offset = -1;

    /**
     * File Stream
     *
     * @var Stream
     */
    protected $stream;

    /**
     * Flag to see if we created an instance using a stream
     */
    private bool $isStream = false;

    /**
     * Create a new DropboxFile instance
     *
     * @param string $path Path of the file to upload
     * @param string $mode     The type of access
     */
    public function __construct(
        /**
         * Path of the file to upload
         */
        protected $path,
        /**
         * The type of access
         */
        protected $mode = self::MODE_READ
    )
    {
    }

    /**
     * Create a new DropboxFile instance using a file stream
     *
     * @param        $fileName
     * @param        $resource
     * @param string $mode
     *
     * @throws DropboxClientException
     */
    public static function createByStream($fileName, $resource, $mode = self::MODE_READ): self
    {
        // create a new stream and set it to the dropbox file
        $stream = Utils::streamFor($resource);
        if (!$stream) {
            throw new DropboxClientException('Failed to create DropboxFile instance. Unable to open the given resource.');
        }

        // Try to get the file path from the stream (we'll need this for uploading bigger files)
        $filePath = $stream->getMetadata('uri');
        if (!is_null($filePath)) {
            $fileName = $filePath;
        }

        $dropboxFile = new self($fileName, $mode);
        $dropboxFile->setStream($stream);

        return $dropboxFile;
    }

    /**
     * Create a new DropboxFile instance using a file path
     *
     * This behaves the same as the constructor but was added in order to
     * match the syntax of the static createByStream function
     *
     * @see DropboxFile::createByStream()
     *
     * @param $filePath
     * @param $mode
     */
    public static function createByPath($filePath, $mode): self
    {
        return new self($filePath, $mode);
    }

    /**
     * Closes the stream when destructed.
     */
    public function __destruct()
    {
        $this->close();
    }

    /**
     * Close the file stream
     */
    public function close(): void
    {
        if ($this->stream) {
            $this->stream->close();
        }
    }

    /**
     * Set the offset to start reading
     * the data from the stream
     *
     * @param int $offset
     */
    public function setOffset($offset): void
    {
        $this->offset = $offset;
    }

    /**
     * Set the Max Length till where to read
     * the data from the stream.
     *
     * @param int $maxLength
     */
    public function setMaxLength($maxLength): void
    {
        $this->maxLength = $maxLength;
    }

    /**
     * Return the contents of the file
     *
     * @throws DropboxClientException
     */
    public function getContents(): string
    {
        $stream = $this->getStream();
        // If an offset is provided
        if ($this->offset !== -1) {
            // Seek to the offset
            $stream->seek($this->offset);
        }

        // If a max length is provided
        if ($this->maxLength !== -1) {
            // Read from the offset till the maxLength
            return $stream->read($this->maxLength);
        }

        return $stream->getContents();
    }

    /**
     * Get the Open File Stream
     *
     * @return Stream
     * @throws DropboxClientException
     */
    public function getStream()
    {
        if (!$this->stream) {
            $this->open();
        }

        return $this->stream;
    }

    /**
     * Manually set the stream for this DropboxFile instance
     *
     * @param $stream
     */
    public function setStream($stream): void
    {
        $this->isStream = true;
        $this->stream = $stream;
    }

    /**
     * Opens the File Stream
     *
     * @throws DropboxClientException
     */
    public function open(): void
    {
        // File was created from a stream so don't open it again
        if ($this->isCreatedFromStream()) {
            return;
        }

        // File is not a remote file
        if (!$this->isRemoteFile($this->path)) {
            // File is not Readable
            if ($this->isNotReadable()) {
                throw new DropboxClientException('Failed to create DropboxFile instance. Unable to read resource: ' . $this->path . '.');
            }

            // File is not Writable
            if ($this->isNotWritable()) {
                throw new DropboxClientException('Failed to create DropboxFile instance. Unable to write resource: ' . $this->path . '.');
            }
        }

        // Create a stream
        $this->stream = Utils::streamFor(fopen($this->path, $this->mode));

        // Unable to create stream
        if (!$this->stream) {
            throw new DropboxClientException('Failed to create DropboxFile instance. Unable to open resource: ' . $this->path . '.');
        }
    }

    protected function isCreatedFromStream(): bool
    {
        return $this->stream && $this->isStream === true;
    }

    /**
     * Returns true if the path to the file is remote
     *
     * @param string $pathToFile
     */
    protected function isRemoteFile($pathToFile): bool
    {
        return preg_match('/^(https?|ftp):\/\/.*/', $pathToFile) === 1;
    }

    protected function isNotReadable(): bool
    {
        return self::MODE_READ === $this->mode && !is_readable($this->path);
    }

    protected function isNotWritable(): bool
    {
        return self::MODE_WRITE === $this->mode && file_exists($this->path) && !is_writable($this->path);
    }

    /**
     * Get the name of the file
     */
    public function getFileName(): string
    {
        return basename($this->path);
    }

    /**
     * Get the path of the file
     *
     * @return string
     */
    public function getFilePath()
    {
        return $this->path;
    }

    public function getStreamOrFilePath()
    {
        return $this->isCreatedFromStream() ? $this->getStream() : $this->getFilePath();
    }

    /**
     * Get the mode of the file stream
     *
     * @return string
     */
    public function getMode()
    {
        return $this->mode;
    }

    /**
     * Get the size of the file
     *
     * @return int
     * @throws DropboxClientException
     */
    public function getSize(): ?int
    {
        return $this->getStream()->getSize();
    }

    /**
     * Get mimetype of the file
     */
    public function getMimetype(): string
    {
        return MimeType::fromFilename($this->path) ?: 'text/plain';
    }
}
