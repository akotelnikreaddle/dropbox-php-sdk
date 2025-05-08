<?php

declare(strict_types=1);

namespace Kunnu\Dropbox;

class DropboxApp
{

    /**
     * Create a new Dropbox instance
     *
     * @param string $clientId     Application Client ID
     * @param string $clientSecret Application Client Secret
     * @param string $accessToken  Access Token
     */
    public function __construct(
        /**
         * The Client ID of the App
         *
         * @link https://www.dropbox.com/developers/apps
         */
        protected $clientId,
        /**
         * The Client Secret of the App
         *
         * @link https://www.dropbox.com/developers/apps
         */
        protected $clientSecret,
        /**
         * The Access Token
         */
        protected $accessToken = null
    )
    {
    }

    /**
     * Get the App Client ID
     *
     * @return string
     */
    public function getClientId()
    {
        return $this->clientId;
    }

    /**
     * Get the App Client Secret
     *
     * @return string
     */
    public function getClientSecret()
    {
        return $this->clientSecret;
    }

    /**
     * Get the Access Token
     *
     * @return string
     */
    public function getAccessToken()
    {
        return $this->accessToken;
    }
}
