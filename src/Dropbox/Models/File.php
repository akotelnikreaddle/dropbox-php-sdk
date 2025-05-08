<?php

declare(strict_types=1);

namespace Kunnu\Dropbox\Models;

use Kunnu\Dropbox\Exceptions\DropboxClientException;
use Kunnu\Dropbox\DropboxFile;

class File extends BaseModel
{

    /**
     * File Metadata
     */
    protected FileMetadata $metadata;


    /**
     * Create a new File instance
     *
     * @param string|DropboxFile $contents
     */
    public function __construct(array $data, /**
     * The file contents
     */
    protected $contents)
    {
        parent::__construct($data);
        $this->metadata = new FileMetadata($data);
    }

    /**
     * The metadata for the file
     *
     * @return FileMetadata
     */
    public function getMetadata()
    {
        return $this->metadata;
    }

    /**
     * Get the file contents
     *
     * @return string
     * @throws DropboxClientException
     */
    public function getContents()
    {
        if ($this->contents instanceof DropboxFile) {
            return $this->contents->getContents();
        }

        return $this->contents;
    }
}
