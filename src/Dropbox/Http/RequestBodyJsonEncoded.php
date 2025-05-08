<?php

declare(strict_types=1);

namespace Kunnu\Dropbox\Http;

/**
 * RequestBodyJsonEncoded
 */
class RequestBodyJsonEncoded implements RequestBodyInterface
{

    /**
     * Create a new RequestBodyJsonEncoded instance
     *
     * @param array $params Request Params
     */
    public function __construct(protected array $params = [])
    {
    }

    /**
     * Get the Body of the Request
     *
     * @return string|null
     */
    public function getBody()
    {
        //Empty body
        if ($this->params === []) {
            return null;
        }

        return json_encode($this->params);
    }
}
