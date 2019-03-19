<?php

namespace Neoflow\CacheFacade;

use Cache\Adapter\Common\AbstractCachePool;
use Cache\Adapter\Void\VoidCachePool;
use Cache\Prefixed\PrefixedCachePool;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Cache\InvalidArgumentException;

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
        array_merge($options, $this->options);

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
     * @return AbstractCachePool Cache pool instance
     */
    public function getCachePool(): AbstractCachePool
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
     *
     * @throws InvalidArgumentException
     */
    public function fetch(string $key, $default = null)
    {
        $item = $this->cachePool->getItem($key);
        if (!$item->isHit()) {
            return $default;
        }

        return $item->get();
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
     *
     * @throws InvalidArgumentException
     */
    public function store(string $key, $value, int $ttl = null, array $tags = []): bool
    {
        $item = $this->cachePool
            ->getItem($key)
            ->set($value)
            ->expiresAfter($ttl)
            ->setTags($tags);

        return $this->cachePool->save($item);
    }

    /**
     * Delete cache value by key.
     *
     * @param string $key Cache key
     *
     * @return bool
     *
     * @throws InvalidArgumentException
     */
    public function delete(string $key): bool
    {
        return $this->cachePool->deleteItem($key);
    }

    /**
     * Check whether cache value exists by key.
     *
     * @param string $key Cache key
     *
     * @return bool
     *
     * @throws InvalidArgumentException
     */
    public function exists(string $key): bool
    {
        return $this->cachePool->hasItem($key);
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
     *
     * @throws InvalidArgumentException
     */
    public function storeDeferred(string $key, $value, int $ttl = null, array $tags = []): bool
    {
        $tags = array_replace($this->options['globalTags'], $tags);

        $item = $this->cachePool
            ->getItem($key)
            ->set($value)
            ->expiresAfter($ttl)
            ->setTags($tags);

        return $this->cachePool->saveDeferred($item);
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
