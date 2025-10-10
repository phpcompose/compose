<?php

namespace Compose\Template\Helper;

interface HelperRegistryAwareInterface
{
    public function setHelperRegistry(HelperRegistryInterface $registry): void;
}
