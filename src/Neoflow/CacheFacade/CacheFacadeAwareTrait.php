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
    public function setCacheFacade(CacheFacadeInterface $cache)
    {
        $this->cache = $cache;
    }
}
