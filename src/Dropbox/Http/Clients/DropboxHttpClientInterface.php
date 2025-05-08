<?php

declare(strict_types=1);

namespace Kunnu\Dropbox\Http\Clients;

use Psr\Http\Message\StreamInterface;
use Kunnu\Dropbox\Http\DropboxRawResponse;
use Kunnu\Dropbox\Exceptions\DropboxClientException;

/**
 * DropboxHttpClientInterface
 */
interface DropboxHttpClientInterface
{
    /**
     * Send request to the server and fetch the raw response
     *
     * @param  string $url     URL/Endpoint to send the request to
     * @param  string $method  Request Method
     * @param string|resource|StreamInterface|null $body Request Body
     * @param  array  $headers Request Headers
     * @param  array  $options Additional Options
     *
     * @return DropboxRawResponse Raw response from the server
     *
     * @throws DropboxClientException
     */
    public function send($url, $method, $body, $headers = [], $options = []);
}
