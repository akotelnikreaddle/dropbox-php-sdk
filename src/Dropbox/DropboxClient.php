<?php

declare(strict_types=1);

namespace Kunnu\Dropbox;

use Kunnu\Dropbox\Exceptions\DropboxClientException;
use Kunnu\Dropbox\Http\Clients\DropboxHttpClientInterface;

/**
 * DropboxClient
 */
class DropboxClient
{
    /**
     * Dropbox API Root URL.
     *
     * @const string
     */
    const BASE_PATH = 'https://api.dropboxapi.com/2';

    /**
     * Dropbox API Content Root URL.
     *
     * @const string
     */
    const CONTENT_PATH = 'https://content.dropboxapi.com/2';

    /**
     * DropboxHttpClientInterface Implementation
     *
     * @var DropboxHttpClientInterface
     */
    protected $httpClient;

    /**
     * Create a new DropboxClient instance
     */
    public function __construct(DropboxHttpClientInterface $httpClient)
    {
        //Set the HTTP Client
        $this->setHttpClient($httpClient);
    }

    /**
     * Get the HTTP Client
     *
     * @return DropboxHttpClientInterface $httpClient
     */
    public function getHttpClient()
    {
        return $this->httpClient;
    }

    /**
     * Set the HTTP Client
     *
     *
     */
    public function setHttpClient(DropboxHttpClientInterface $httpClient): static
    {
        $this->httpClient = $httpClient;

        return $this;
    }

    /**
     * Get the API Base Path.
     *
     * @return string API Base Path
     */
    public function getBasePath()
    {
        return static::BASE_PATH;
    }

    /**
     * Get the API Content Path.
     *
     * @return string API Content Path
     */
    public function getContentPath()
    {
        return static::CONTENT_PATH;
    }

    /**
     * Get the Authorization Header with the Access Token.
     *
     * @param string $accessToken Access Token
     *
     * @return array Authorization Header
     */
    protected function buildAuthHeader(string $accessToken = ""): array
    {
        return ['Authorization' => 'Bearer '. $accessToken];
    }

    /**
     * Get the Content Type Header.
     *
     * @param string $contentType Request Content Type
     *
     * @return array Content Type Header
     */
    protected function buildContentTypeHeader($contentType = ""): array
    {
        return ['Content-Type' => $contentType];
    }

    /**
     * Build URL for the Request
     *
     * @param string $endpoint Relative API endpoint
     * @param string $type Endpoint Type
     *
     * @link https://www.dropbox.com/developers/documentation/http/documentation#formats Request and response formats
     *
     * @return string The Full URL to the API Endpoints
     */
    protected function buildUrl(string $endpoint = '', $type = 'api'): string
    {
        //Get the base path
        $base = $this->getBasePath();

        //If the endpoint type is 'content'
        if ($type === 'content') {
            //Get the Content Path
            $base = $this->getContentPath();
        }

        //Join and return the base and api path/endpoint
        return $base . $endpoint;
    }

    /**
     * Send the Request to the Server and return the Response
     *
     *
     * @return DropboxResponse
     *
     * @throws DropboxClientException
     */
    public function sendRequest(DropboxRequest $request, ?DropboxResponse $response = null)
    {
        //Method
        $method = $request->getMethod();

        //Prepare Request
        [$url, $headers, $requestBody] = $this->prepareRequest($request);

        $options = [];
        if ($response instanceof DropboxResponseToFile) {
            $options['sink'] = $response->getSteamOrFilePath();
        }

        //Send the Request to the Server through the HTTP Client
        //and fetch the raw response as DropboxRawResponse
        $rawResponse = $this->getHttpClient()->send($url, $method, $requestBody, $headers, $options);

        //Create DropboxResponse from DropboxRawResponse
        $response = $response ?: new DropboxResponse($request);
        $response->setHttpStatusCode($rawResponse->getHttpResponseCode());
        $response->setHeaders($rawResponse->getHeaders());
        if (!$response instanceof DropboxResponseToFile) {
            $response->setBody($rawResponse->getBody());
        }

        //Return the DropboxResponse
        return $response;
    }

    /**
     * Prepare a Request before being sent to the HTTP Client
     *
     *
     * @return array [Request URL, Request Headers, Request Body]
     */
    protected function prepareRequest(DropboxRequest $request): array
    {
        //Build URL
        $url = $this->buildUrl($request->getEndpoint(), $request->getEndpointType());

        //The Endpoint is content
        if ($request->getEndpointType() === 'content') {
            //Dropbox requires the parameters to be passed
            //through the 'Dropbox-API-Arg' header
            $request->setHeaders(['Dropbox-API-Arg' => json_encode($request->getParams())]);

            //If a File is also being uploaded
            if ($request->hasFile()) {
                //Content Type
                $request->setContentType("application/octet-stream");

                //Request Body (File Contents)
                $requestBody = $request->getStreamBody()->getBody();
            } else {
                //Empty Body
                $requestBody = null;
            }
        } else {
            //The endpoint is 'api'
            //Request Body (Parameters)
            $requestBody = $request->getJsonBody()->getBody();
        }

        //Empty body
        if (is_null($requestBody)) {
            //Content Type needs to be kept empty
            $request->setContentType("");
        }

        //Build headers
        $headers = array_merge(
            $this->buildAuthHeader($request->getAccessToken()),
            $this->buildContentTypeHeader($request->getContentType()),
            $request->getHeaders()
        );

        //Return the URL, Headers and Request Body
        return [$url, $headers, $requestBody];
    }
}
