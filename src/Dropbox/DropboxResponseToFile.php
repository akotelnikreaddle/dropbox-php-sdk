<?php

declare(strict_types=1);

namespace Kunnu\Dropbox;

class DropboxResponseToFile extends DropboxResponse
{
    /**
     * Create a new DropboxResponse instance
     *
     * @param int|null    $httpStatusCode
     */
    public function __construct(DropboxRequest $request, protected DropboxFile $file, $httpStatusCode = null, array $headers = [])
    {
        parent::__construct($request, null, $httpStatusCode, $headers);
    }

    /**
     * @throws Exceptions\DropboxClientException
     */
    #[\Override]
    public function getBody()
    {
        return $this->file->getContents();
    }

    public function getFilePath()
    {
        return $this->file->getFilePath();
    }

    public function getSteamOrFilePath()
    {
        return $this->file->getStreamOrFilePath();
    }
}
