<?php

declare(strict_types=1);

namespace Kunnu\Dropbox\Http;

use Kunnu\Dropbox\DropboxFile;

/**
 * RequestBodyStream
 */
class RequestBodyStream implements RequestBodyInterface
{

    /**
     * Create a new RequestBodyStream instance
     */
    public function __construct(
        /**
         * File to be sent with the Request
         */
        protected DropboxFile $file
    )
    {
    }

    /**
     * Get the Body of the Request
     *
     * @return string
     */
    public function getBody()
    {
        return $this->file->getContents();
    }
}
