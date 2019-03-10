<?php

namespace Neoflow\CacheFacade;

interface CacheAwareInterface
{
    /**
     * Set cache facade.
     *
     * @param CacheFacadeInterface $cache Cache facade instance
     */
    public function setCache(CacheFacadeInterface $cache);
}
