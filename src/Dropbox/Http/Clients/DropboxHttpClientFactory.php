<?php

declare(strict_types=1);

namespace Kunnu\Dropbox\Http\Clients;

use InvalidArgumentException;
use GuzzleHttp\Client as Guzzle;

/**
 * DropboxHttpClientFactory
 */
class DropboxHttpClientFactory
{
    /**
     * Make HTTP Client
     *
     * @param DropboxHttpClientInterface|\GuzzleHttp\Client|null $handler
     */
    public static function make($handler): DropboxGuzzleHttpClient|DropboxHttpClientInterface
    {
        //No handler specified
        if (!$handler) {
            return new DropboxGuzzleHttpClient();
        }

        //Custom Implementation, maybe.
        if ($handler instanceof DropboxHttpClientInterface) {
            return $handler;
        }

        //Handler is a custom configured Guzzle Client
        if ($handler instanceof Guzzle) {
            return new DropboxGuzzleHttpClient($handler);
        }

        //Invalid handler
        throw new InvalidArgumentException('The http client handler must be an instance of GuzzleHttp\Client or an instance of Kunnu\Dropbox\Http\Clients\DropboxHttpClientInterface.');
    }
}
