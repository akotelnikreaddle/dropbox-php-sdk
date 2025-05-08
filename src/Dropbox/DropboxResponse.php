<?php

declare(strict_types=1);

namespace Kunnu\Dropbox;

use Kunnu\Dropbox\Exceptions\DropboxClientException;

class DropboxResponse
{
    /**
     *  The decoded body of the response
     *
     * @var array
     */
    protected $decodedBody = [];

    /**
     * Create a new DropboxResponse instance
     *
     * @param string|null $body
     * @param int|null    $httpStatusCode
     */
    public function __construct(
        /**
         * The original request that returned this response
         */
        protected DropboxRequest $request,
        /**
         *  The raw body of the response
         */
        protected $body = null,
        /**
         *  The HTTP status code response
         */
        protected $httpStatusCode = null,
        /**
         *  The headers returned
         */
        protected array $headers = []
    )
    {
    }

    /**
     * @param string $body
     */
    public function setBody($body): void
    {
        $this->body = $body;
    }

    /**
     * @param int $httpStatusCode
     */
    public function setHttpStatusCode($httpStatusCode): void
    {
        $this->httpStatusCode = $httpStatusCode;
    }

    public function setHeaders(array $headers): void
    {
        $this->headers = $headers;
    }

    /**
     * Get the Request Request
     *
     * @return DropboxRequest
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * Get the Response Body
     *
     * @return string
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * Get the Decoded Body
     *
     * @return array
     * @throws DropboxClientException
     */
    public function getDecodedBody()
    {
        if ($this->decodedBody === [] || $this->decodedBody === null) {
            //Decode the Response Body
            $this->decodeBody();
        }

        return $this->decodedBody;
    }

    /**
     * Get Access Token for the Request
     *
     * @return string
     */
    public function getAccessToken()
    {
        return $this->getRequest()->getAccessToken();
    }

    /**
     * Get Request Headers
     *
     * @return array
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * Get the HTTP Status Code
     *
     * @return int
     */
    public function getHttpStatusCode()
    {
        return $this->httpStatusCode;
    }

    /**
     * Decode the Body
     *
     * @throws DropboxClientException
     *
     * @return void
     */
    protected function decodeBody()
    {
        $body = $this->getBody();

        if (isset($this->headers['Content-Type']) && in_array('application/json', $this->headers['Content-Type'])) {
            $this->decodedBody = (array) json_decode((string) $body, true);
        }

        // If the response needs to be validated
        if ($this->getRequest()->validateResponse()) {
            //Validate Response
            $this->validateResponse();
        }
    }

    /**
     * Validate Response
     *
     * @return void
     *
     * @throws DropboxClientException
     */
    protected function validateResponse()
    {
        // If JSON cannot be decoded
        if ($this->decodedBody === null) {
            throw new DropboxClientException("Invalid Response");
        }
    }
}
