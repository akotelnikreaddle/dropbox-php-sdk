<?php

declare(strict_types=1);

namespace Kunnu\Dropbox\Store;

use InvalidArgumentException;

/**
 * Thanks to Facebook
 *
 * @link https://developers.facebook.com/docs/php/PersistentDataInterface
 */
class PersistentDataStoreFactory
{
    /**
     * Make Persistent Data Store
     *
     * @param null|string|PersistentDataStoreInterface $store
     *
     * @throws InvalidArgumentException
     */
    public static function makePersistentDataStore($store = null): SessionPersistentDataStore|PersistentDataStoreInterface
    {
        if (is_null($store) || $store === 'session') {
            return new SessionPersistentDataStore();
        }

        if ($store instanceof PersistentDataStoreInterface) {
            return $store;
        }

        throw new InvalidArgumentException('The persistent data store must be set to null, "session" or be an instance of use \Kunnu\Dropbox\Store\PersistentDataStoreInterface');
    }
}
