<?php

namespace Compose\Event;

use Psr\Container\ContainerInterface;

final class ApplicationReadyEvent
{
    public function __construct(private ContainerInterface $container)
    {
    }

    public function container(): ContainerInterface
    {
        return $this->container;
    }
}
