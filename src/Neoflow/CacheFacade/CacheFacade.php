<?php

namespace Neoflow\CacheFacade;

use Cache\Adapter\Common\AbstractCachePool;

class CacheFacade implements CacheFacadeInterface
{
    /**
     * @var AbstractCachePool
     */
    protected $cachePool;

    /**
     * @var array
     */
    protected $options = [
        'autoCommit' => true,
        'globalTags' => [],
        'defaultTtl' => 0,
    ];

    /**
     * Constructor.
     *
     * @param AbstractCachePool $cachePool Cache pool instance
     */
    public function __construct(AbstractCachePool $cachePool, array $options = [])
    {
        array_merge($this->options, $options);

        $this->cachePool = $cachePool;
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
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function fetch(string $key, $default = null)
    {
        return $this->cachePool->get($key, $default);
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
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function store(string $key, $value, int $ttl = 0, array $tags = []): bool
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
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function delete(string $key): bool
    {
        return $this->cachePool->delete($key);
    }

    /**
     * Check whether cache value exists by key.
     *
     * @param string $key Cache key
     *
     * @return bool
     *
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function exists(string $key): bool
    {
        return $this->cachePool->has($key);
    }

    /**
     * Clear complete cache.
     *
     * @return bool
     */
    public function clear(): bool
    {
        return $this->cachePool->commit();
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
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function storeDeferred(string $key, $value, int $ttl = null, array $tags = []): bool
    {
        $tags = array_replace($this->options['globalTags'], $tags);

        if (null === $ttl) {
            $ttl = $this->options['defaultTtl'];
        }

        $item = $this->cachePool
            ->getItem($key)
            ->set($value)
            ->expiresAfter($ttl)
            ->setTags($tags);

        return $this->cachePool->saveDeferred($item);
    }

    /**
     * Delete cache values by tag.
     *
     * @param string $tag Tag of the values
     *
     * @return bool
     */
    public function deleteByTag(string $tag): bool
    {
        return $this->cachePool->invalidateTags($tag);
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
