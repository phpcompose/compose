<?php
namespace Compose\Container;


use Psr\Container\ContainerInterface;

/**
 * Interface ServiceFactoryInterface
 * @package Compose\Store
 */
interface ServiceFactoryInterface extends ResolvableInterface
{
    /**
     * MUST return object
     *
     * @param ContainerInterface $container
     * @return object
     */
    static public function create(ContainerInterface $container, string $name);
}