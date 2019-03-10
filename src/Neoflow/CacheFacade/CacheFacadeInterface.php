<?php

namespace Neoflow\CacheFacade;

interface CacheFacadeInterface
{
    /**
     * Fetch cache value by key.
     *
     * @param string $key Cache key
     * @param mixed $default Default value
     *
     * @return mixed
     */
    public function fetch(string $key, $default = null);

    /**
     * Store cache value by key.
     *
     * @param string $key Cache key
     * @param mixed $value Value to cache
     * @param int $ttl Cache lifetime
     * @param array $tags Cache tags
     *
     * @return bool
     */
    public function store(string $key, $value, int $ttl = 0, array $tags = []): bool;

    /**
     * Delete cache value by key.
     *
     * @param string $key Cache key
     *
     * @return bool
     */
    public function delete(string $key): bool;

    /**
     * Check whether cache value exists by key.
     *
     * @param string $key Cache key
     *
     * @return bool
     */
    public function exists(string $key): bool;

    /**
     * Clear complete cache.
     *
     * @return bool
     */
    public function clear(): bool;

    /**
     * Store cache value by key deferred, when the destructor gets called.
     *
     * @param string $key Cache key
     * @param mixed $value Value to cache
     * @param int $ttl Cache lifetime
     * @param array $tags Cache tags
     *
     * @return bool
     */
    public function storeDeferred(string $key, $value, int $ttl = 0, array $tags = []): bool;

    /**
     * Delete cache values by tag.
     *
     * @param string $tag Tag of the values
     *
     * @return bool
     */
    public function deleteByTag(string $tag): bool;
}
