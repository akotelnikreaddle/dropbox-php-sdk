<?php

declare(strict_types=1);

namespace Kunnu\Dropbox;

use Kunnu\Dropbox\Http\RequestBodyStream;
use Kunnu\Dropbox\Http\RequestBodyJsonEncoded;

/**
 * DropboxRequest
 */
class DropboxRequest
{

    /**
     * Access Token to use for this request
     *
     * @var string
     */
    protected $accessToken;

    /**
     * The HTTP method for this request
     *
     * @var string
     */
    protected $method = "GET";

    /**
     * The params for this request
     *
     * @var array
     */
    protected $params;

    /**
     * The Endpoint for this request
     *
     * @var string
     */
    protected $endpoint;

    /**
     * The Endpoint Type for this request
     *
     * @var string
     */
    protected $endpointType;

    /**
     * The headers to send with this request
     *
     * @var array
     */
    protected $headers = [];

    /**
     * File to upload
     *
     * @var DropboxFile
     */
    protected $file;

    /**
     * Content Type for the Request
     *
     * @var string
     */
    protected $contentType = 'application/json';

    /**
     * If the Response needs to be validated
     * against being a valid JSON response.
     * Set this to false when an endpoint or
     * request has no return values.
     *
     * @var boolean
     */
    protected $validateResponse = true;


    /**
     * Create a new DropboxRequest instance
     *
     * @param string $method       HTTP Method of the Request
     * @param string $endpoint     API endpoint of the Request
     * @param string $accessToken  Access Token for the Request
     * @param string $endpointType Endpoint type ['api'|'content']
     * @param mixed  $params       Request Params
     * @param array  $headers      Headers to send along with the Request
     */
    public function __construct($method, $endpoint, $accessToken, $endpointType = "api", array $params = [], array $headers = [], $contentType = null)
    {
        $this->setMethod($method);
        $this->setEndpoint($endpoint);
        $this->setAccessToken($accessToken);
        $this->setEndpointType($endpointType);
        $this->setParams($params);
        $this->setHeaders($headers);

        if ($contentType) {
            $this->setContentType($contentType);
        }
    }

    /**
     * Get the Request Method
     *
     * @return string
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * Set the Request Method
     *
     * @param string
     */
    public function setMethod($method): static
    {
        $this->method = $method;

        return $this;
    }

    /**
     * Get Access Token for the Request
     *
     * @return string
     */
    public function getAccessToken()
    {
        return $this->accessToken;
    }

    /**
     * Set Access Token for the Request
     *
     * @param string
     */
    public function setAccessToken($accessToken): static
    {
        $this->accessToken = $accessToken;

        return $this;
    }

    /**
     * Get the Endpoint of the Request
     *
     * @return string
     */
    public function getEndpoint()
    {
        return $this->endpoint;
    }

    /**
     * Set the Endpoint of the Request
     *
     * @param string
     */
    public function setEndpoint($endpoint): static
    {
        $this->endpoint = $endpoint;

        return $this;
    }

    /**
     * Get the Endpoint Type of the Request
     *
     * @return string
     */
    public function getEndpointType()
    {
        return $this->endpointType;
    }

    /**
     * Set the Endpoint Type of the Request
     *
     * @param string
     */
    public function setEndpointType($endpointType): static
    {
        $this->endpointType = $endpointType;

        return $this;
    }

    /**
     * Get the Content Type of the Request
     *
     * @return string
     */
    public function getContentType()
    {
        return $this->contentType;
    }

    /**
     * Set the Content Type of the Request
     *
     * @param string
     */
    public function setContentType($contentType): static
    {
        $this->contentType = $contentType;

        return $this;
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
     * Set Request Headers
     *
     * @param array
     */
    public function setHeaders(array $headers): static
    {
        $this->headers = array_merge($this->headers, $headers);

        return $this;
    }

    /**
     * Get JSON Encoded Request Body
     */
    public function getJsonBody(): RequestBodyJsonEncoded
    {
        return new RequestBodyJsonEncoded($this->getParams());
    }

    /**
     * Get the Request Params
     *
     * @return array
     */
    public function getParams()
    {
        return $this->params;
    }

    /**
     * Set the Request Params
     *
     * @param array
     */
    public function setParams(array $params = []): static
    {

        //Process Params
        $params = $this->processParams($params);

        //Set the params
        $this->params = $params;

        return $this;
    }

    /**
     * Get Stream Request Body
     */
    public function getStreamBody(): RequestBodyStream
    {
        return new RequestBodyStream($this->getFile());
    }

    /**
     * Get the File to be sent with the Request
     *
     * @return DropboxFile
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * Set the File to be sent with the Request
     *
     * @param \Kunnu\Dropbox\DropboxFile
     */
    public function setFile(DropboxFile $file): static
    {
        $this->file = $file;

        return $this;
    }

    /**
     * Returns true if Request has file to be uploaded
     */
    public function hasFile(): true
    {
        return !is_null($this->file);
    }

    /**
     * Whether to validate response or not
     *
     * @return boolean
     */
    public function validateResponse()
    {
        return $this->validateResponse;
    }

    /**
     * Process Params for the File parameter
     *
     * @param  array $params Request Params
     */
    protected function processParams(array $params): array
    {
        //If a file needs to be uploaded
        if (isset($params['file']) && $params['file'] instanceof DropboxFile) {
            //Set the file property
            $this->setFile($params['file']);
            //Remove the file item from the params array
            unset($params['file']);
        }

        //Whether the response needs to be validated
        //against being a valid JSON response
        if (isset($params['validateResponse'])) {
            //Set the validateResponse
            $this->validateResponse = $params['validateResponse'];
            //Remove the validateResponse from the params array
            unset($params['validateResponse']);
        }

        return $params;
    }
}
