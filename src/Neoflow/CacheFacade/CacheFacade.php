<?php

namespace Neoflow\CacheFacade;

use Cache\Adapter\Void\VoidCachePool;
use Cache\Prefixed\PrefixedCachePool;
use Psr\Cache\CacheException;
use Psr\Cache\CacheItemPoolInterface;

class CacheFacade implements CacheFacadeInterface
{
    /**
     * @var CacheItemPoolInterface
     */
    protected $cachePool;

    /**
     * @var array
     */
    protected $options = [
        'autoCommit' => true,
        'prefix' => '',
    ];

    /**
     * Constructor.
     *
     * @param CacheItemPoolInterface $cachePool Cache pool instance
     * @param array                  $options   Custom options
     */
    public function __construct(CacheItemPoolInterface $cachePool = null, array $options = [])
    {
        $this->options = array_merge($this->options, $options);

        if ($cachePool) {
            $this->cachePool = $cachePool;
        } else {
            $this->cachePool = new VoidCachePool();
        }

        if ($this->options['prefix']) {
            $this->cachePool = new PrefixedCachePool($this->cachePool, preg_replace('/[^A-Za-z0-9]/', '', $this->options['prefix']));
        }
    }

    /**
     * Get cache pool.
     *
     * @return CacheItemPoolInterface
     */
    public function getCachePool(): CacheItemPoolInterface
    {
        return $this->cachePool;
    }

    /**
     * Fetch cache value by key.
     *
     * @param string $key     Cache key
     * @param mixed  $default Default value
     *
     * @return mixed
     */
    public function fetch(string $key, $default = null)
    {
        try {
            $item = $this->cachePool->getItem($key);
            if (!$item->isHit()) {
                return $default;
            }

            return $item->get();
        } catch (CacheException $ex) {
            return $default;
        }
    }

    /**
     * Store cache value by key.
     *
     * @param string $key   Cache key
     * @param mixed  $value Value to cache
     * @param int    $ttl   Cache lifetime
     * @param array  $tags  Cache tags
     *
     * @return bool
     */
    public function store(string $key, $value, int $ttl = null, array $tags = []): bool
    {
        try {
            $item = $this->cachePool
                ->getItem($key)
                ->set($value)
                ->expiresAfter($ttl)
                ->setTags($tags);

            return $this->cachePool->save($item);
        } catch (CacheException $ex) {
            return false;
        }
    }

    /**
     * Delete cache value by key.
     *
     * @param string $key Cache key
     *
     * @return bool
     */
    public function delete(string $key): bool
    {
        try {
            return $this->cachePool->deleteItem($key);
        } catch (CacheException $ex) {
            return false;
        }
    }

    /**
     * Check whether cache value exists by key.
     *
     * @param string $key Cache key
     *
     * @return bool
     */
    public function exists(string $key): bool
    {
        try {
            return $this->cachePool->hasItem($key);
        } catch (CacheException $ex) {
            return false;
        }
    }

    /**
     * Clear complete cache.
     *
     * @return bool
     */
    public function clear(): bool
    {
        return $this->cachePool->clear();
    }

    /**
     * Store cache value by key deferred, when the destructor gets called.
     *
     * @param string $key   Cache key
     * @param mixed  $value Value to cache
     * @param int    $ttl   Time to live in milliseconds)
     * @param array  $tags  An array of tags
     *
     * @return bool
     */
    public function storeDeferred(string $key, $value, int $ttl = null, array $tags = []): bool
    {
        try {
            $tags = array_replace($this->options['globalTags'], $tags);

            $item = $this->cachePool
                ->getItem($key)
                ->set($value)
                ->expiresAfter($ttl)
                ->setTags($tags);

            return $this->cachePool->saveDeferred($item);
        } catch (CacheException $ex) {
            return false;
        }
    }

    /**
     * Delete cache values by tags.
     *
     * @param array $tags Tags of the values
     *
     * @return bool
     */
    public function deleteByTags(array $tags): bool
    {
        return $this->cachePool->invalidateTags($tags);
    }

    /**
     * Destructor.
     */
    public function __destruct()
    {
        if ($this->options['autoCommit']) {
            $this->cachePool->commit();
        }
    }
}
