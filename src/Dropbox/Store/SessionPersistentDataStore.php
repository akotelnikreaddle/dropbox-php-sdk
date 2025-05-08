<?php

declare(strict_types=1);

namespace Kunnu\Dropbox\Store;

class SessionPersistentDataStore implements PersistentDataStoreInterface
{

    /**
     * Create a new SessionPersistentDataStore instance
     *
     * @param string $prefix Session Variable Prefix
     */
    public function __construct(
        /**
         * Session Variable Prefix
         */
        protected $prefix = "DBAPI_"
    )
    {
    }

    /**
     * Get a value from the store
     *
     * @param  string $key Data Key
     *
     * @return string|null
     */
    public function get($key)
    {
        return $_SESSION[$this->prefix . $key] ?? null;
    }

    /**
     * Set a value in the store
     * @param string $key   Data Key
     * @param string $value Data Value
     */
    public function set($key, $value): void
    {
        $_SESSION[$this->prefix . $key] = $value;
    }

    /**
     * Clear the key from the store
     *
     * @param string $key Data Key
     */
    public function clear($key): void
    {
        if (isset($_SESSION[$this->prefix . $key])) {
            unset($_SESSION[$this->prefix . $key]);
        }
    }
}
