<?php

namespace Neoflow\CacheFacade;

trait CacheFacadeAwareTrait
{
    /**
     * @var CacheFacadeInterface
     */
    protected $cache;

    /**
     * Set cache facade.
     *
     * @param CacheFacadeInterface $cache Cache facade instance
     */
    public function setCache(CacheFacadeInterface $cache)
    {
        $this->cache = $cache;
    }
}
