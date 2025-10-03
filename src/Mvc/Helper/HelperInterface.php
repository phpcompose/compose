<?php

namespace Compose\Mvc\Helper;

use Compose\Mvc\HelperRegistryInterface;

interface HelperInterface
{
    /**
     * Invoked by the helper registry with the current rendering context.
     *
     * @param HelperRegistryInterface $helpers
     * @param mixed ...$args
     * @return mixed
     */
    public function __invoke(HelperRegistryInterface $helpers, ...$args);
}
