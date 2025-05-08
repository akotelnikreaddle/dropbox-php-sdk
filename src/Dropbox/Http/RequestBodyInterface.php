<?php

declare(strict_types=1);

namespace Kunnu\Dropbox\Http;

use Psr\Http\Message\StreamInterface;

/**
 * RequestBodyInterface
 */
interface RequestBodyInterface
{
    /**
     * Get the Body of the Request
     *
     * @return string|resource|StreamInterface
     */
    public function getBody();
}
