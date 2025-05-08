<?php

declare(strict_types=1);

namespace Kunnu\Dropbox\Http;

/**
 * DropboxRawResponse
 */
class DropboxRawResponse
{
    /**
     * HTTP status response code
     *
     * @var int
     */
    protected $httpResponseCode;

    /**
     * Create a new GraphRawResponse instance
     *
     * @param array     $headers        Response headers
     * @param string    $body           Raw response body
     * @param int|null  $statusCode     HTTP response code
     */
    public function __construct(/**
     * Response headers
     */
    protected $headers, /**
     * Raw response body
     */
    protected $body, $statusCode = null)
    {
        if (is_numeric($statusCode)) {
            $this->httpResponseCode = (int) $statusCode;
        }
    }

    /**
     * Get the response headers
     *
     * @return array
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * Get the response body
     *
     * @return string
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * Get the HTTP response code
     *
     * @return int
     */
    public function getHttpResponseCode()
    {
        return $this->httpResponseCode;
    }
}
