<?php

declare(strict_types=1);

namespace Kunnu\Dropbox\Authentication;

use Kunnu\Dropbox\Models\AccessToken;
use Kunnu\Dropbox\Exceptions\DropboxClientException;
use Kunnu\Dropbox\Store\PersistentDataStoreInterface;
use Kunnu\Dropbox\Security\RandomStringGeneratorInterface;

class DropboxAuthHelper
{
    /**
     * The length of CSRF string
     *
     * @const int
     */
    const CSRF_LENGTH = 32;

    /**
     * Additional User Provided State
     *
     * @var string
     */
    protected $urlState;

    /**
     * Create a new DropboxAuthHelper instance
     */
    public function __construct(
        /**
         * OAuth2 Client
         */
        protected OAuth2Client $oAuth2Client,
        /**
         * Random String Generator
         */
        protected ?RandomStringGeneratorInterface $randomStringGenerator = null,
        /**
         * Persistent Data Store
         */
        protected ?PersistentDataStoreInterface $persistentDataStore = null
    )
    {
    }

    /**
     * Get OAuth2Client
     *
     * @return OAuth2Client
     */
    public function getOAuth2Client()
    {
        return $this->oAuth2Client;
    }

    /**
     * Get the Random String Generator
     *
     * @return RandomStringGeneratorInterface
     */
    public function getRandomStringGenerator()
    {
        return $this->randomStringGenerator;
    }

    /**
     * Get the Persistent Data Store
     *
     * @return PersistentDataStoreInterface
     */
    public function getPersistentDataStore()
    {
        return $this->persistentDataStore;
    }

    /**
     * Get CSRF Token
     *
     * @return string
     */
    protected function getCsrfToken()
    {
        $generator = $this->getRandomStringGenerator();

        return $generator->generateString(static::CSRF_LENGTH);
    }

    /**
     * Get Authorization URL
     *
     * @param  string $redirectUri Callback URL to redirect to after authorization
     * @param  array  $params      Additional Params
     * @param  string $urlState  Additional User Provided State Data
     * @param string $tokenAccessType Either `offline` or `online` or null
     *
     * @link https://www.dropbox.com/developers/documentation/http/documentation#oauth2-authorize
     *
     * @return string
     */
    public function getAuthUrl($redirectUri = null, array $params = [], $urlState = null, $tokenAccessType = null)
    {
        // If no redirect URI
        // is provided, the
        // CSRF validation
        // is being handled
        // explicitly.
        $state = null;

        // Redirect URI is provided
        // thus, CSRF validation
        // needs to be handled.
        if (!is_null($redirectUri)) {
            //Get CSRF State Token
            $state = $this->getCsrfToken();

            //Set the CSRF State Token in the Persistent Data Store
            $this->getPersistentDataStore()->set('state', $state);

            //Additional User Provided State Data
            if (!is_null($urlState)) {
                $state .= "|";
                $state .= $urlState;
            }
        }

        //Get OAuth2 Authorization URL
        return $this->getOAuth2Client()->getAuthorizationUrl($redirectUri, $state, $params, $tokenAccessType);
    }

    /**
     * Decode State to get the CSRF Token and the URL State
     *
     * @param  string $state State
     */
    protected function decodeState($state): array
    {
        $csrfToken = $state;
        $urlState = null;

        $splitPos = strpos($state, "|");

        if ($splitPos !== false) {
            $csrfToken = substr($state, 0, $splitPos);
            $urlState = substr($state, $splitPos + 1);
        }

        return ['csrfToken' => $csrfToken, 'urlState' => $urlState];
    }

    /**
     * Validate CSRF Token
     * @param  string $csrfToken CSRF Token
     *
     * @throws DropboxClientException
     *
     * @return void
     */
    protected function validateCSRFToken($csrfToken)
    {
        $tokenInStore = $this->getPersistentDataStore()->get('state');

        //Unable to fetch CSRF Token
        if (!$tokenInStore || !$csrfToken) {
            throw new DropboxClientException("Invalid CSRF Token. Unable to validate CSRF Token.");
        }

        //CSRF Token Mismatch
        if ($tokenInStore !== $csrfToken) {
            throw new DropboxClientException("Invalid CSRF Token. CSRF Token Mismatch.");
        }

        //Clear the state store
        $this->getPersistentDataStore()->clear('state');
    }

    /**
     * Get Access Token
     *
     * @param  string $code Authorization Code
     * @param  string $state CSRF & URL State
     * @param  string $redirectUri Redirect URI used while getAuthUrl
     *
     * @throws DropboxClientException
     */
    public function getAccessToken($code, $state = null, $redirectUri = null): AccessToken
    {
        // No state provided
        // Should probably be
        // handled explicitly
        if (!is_null($state)) {
            //Decode the State
            $state = $this->decodeState($state);

            //CSRF Token
            $csrfToken = $state['csrfToken'];

            //Set the URL State
            $this->urlState = $state['urlState'];

            //Validate CSRF Token
            $this->validateCSRFToken($csrfToken);
        }

        //Fetch Access Token
        $accessToken = $this->getOAuth2Client()->getAccessToken($code, $redirectUri);

        //Make and return the model
        return new AccessToken($accessToken);
    }

    /**
     * Get new Access Token by using the refresh token
     *
     * @param AccessToken $accessToken - Current access token object
     * @param string $grantType ['refresh_token']
     */
    public function getRefreshedAccessToken($accessToken, $grantType = 'refresh_token'): AccessToken
    {
        $newToken = $this->getOAuth2Client()->getAccessToken($accessToken->refresh_token, null, $grantType);

        return new AccessToken(
            array_merge(
                $accessToken->getData(),
                $newToken
            )
        );
    }

    /**
     * Revoke Access Token
     *
     * @throws DropboxClientException
     */
    public function revokeAccessToken(): void
    {
        $this->getOAuth2Client()->revokeAccessToken();
    }

    /**
     * Get URL State
     *
     * @return string
     */
    public function getUrlState()
    {
        return $this->urlState;
    }
}
