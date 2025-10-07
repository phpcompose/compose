<?php

declare(strict_types=1);

namespace Compose\Starter\Event;

use Compose\Event\AbstractEvent;
use Psr\Container\ContainerInterface;

final class ApplicationReadyEvent extends AbstractEvent
{
    public function __construct(private ContainerInterface $container)
    {
    }

    public function container(): ContainerInterface
    {
        return $this->container;
    }
}
