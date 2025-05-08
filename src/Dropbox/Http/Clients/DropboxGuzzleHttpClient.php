<?php

declare(strict_types=1);

namespace Kunnu\Dropbox\Http\Clients;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\ResponseInterface;
use GuzzleHttp\Exception\RequestException;
use Kunnu\Dropbox\Http\DropboxRawResponse;
use GuzzleHttp\Exception\BadResponseException;
use Kunnu\Dropbox\Exceptions\DropboxClientException;

/**
 * DropboxGuzzleHttpClient.
 */
class DropboxGuzzleHttpClient implements DropboxHttpClientInterface
{
    /**
     * GuzzleHttp client.
     */
    protected Client $client;

    /**
     * Create a new DropboxGuzzleHttpClient instance.
     *
     * @param Client $client GuzzleHttp Client
     */
    public function __construct(?Client $client = null)
    {
        //Set the client
        $this->client = $client ?: new Client();
    }

    /**
     * Send request to the server and fetch the raw response.
     *
     * @param  string $url     URL/Endpoint to send the request to
     * @param  string $method  Request Method
     * @param  string|resource|StreamInterface $body Request Body
     * @param  array  $headers Request Headers
     * @param  array  $options Additional Options
     *
     * @return DropboxRawResponse Raw response from the server
     *
     * @throws DropboxClientException
     */
    public function send($url, $method, $body, $headers = [], $options = []): DropboxRawResponse
    {
        //Create a new Request Object
        $request = new Request($method, $url, $headers, $body);

        try {
            //Send the Request
            $rawResponse = $this->client->send($request, $options);
        } catch (BadResponseException $e) {
            throw new DropboxClientException($e->getResponse()->getBody()->getContents(), $e->getCode(), $e);
        } catch (RequestException $e) {
            $rawResponse = $e->getResponse();

            if (! $rawResponse instanceof ResponseInterface) {
                throw new DropboxClientException($e->getMessage(), $e->getCode());
            }
        }

        //Something went wrong
        if ($rawResponse->getStatusCode() >= 400) {
            throw new DropboxClientException($rawResponse->getBody()->getContents());
        }

        if (array_key_exists('sink', $options)) {
            //Response Body is saved to a file
            $body = '';
        } else {
            //Get the Response Body
            $body = $this->getResponseBody($rawResponse);
        }

        $rawHeaders = $rawResponse->getHeaders();
        $httpStatusCode = $rawResponse->getStatusCode();

        //Create and return a DropboxRawResponse object
        return new DropboxRawResponse($rawHeaders, $body, $httpStatusCode);
    }

    /**
     * Get the Response Body.
     *
     * @param string|ResponseInterface $response Response object
     */
    protected function getResponseBody($response): string
    {
        //Response must be string
        $body = $response;

        if ($response instanceof ResponseInterface) {
            //Fetch the body
            $body = $response->getBody();
        }

        if ($body instanceof StreamInterface) {
            $body = $body->getContents();
        }

        return (string) $body;
    }
}
