<?php

declare(strict_types=1);

namespace Compose\Container;

use Psr\Container\ContainerInterface;

interface ServiceFactoryInterface extends ResolvableInterface
{
    public static function create(ContainerInterface $container, string $id): object;
}