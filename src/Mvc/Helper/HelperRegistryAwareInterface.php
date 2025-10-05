<?php

namespace Compose\Mvc\Helper;

use Compose\Mvc\HelperRegistryInterface;

interface HelperRegistryAwareInterface
{
    public function setHelperRegistry(HelperRegistryInterface $registry): void;
}
