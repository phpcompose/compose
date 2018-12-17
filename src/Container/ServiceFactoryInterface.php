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
     * @param ContainerInterface $container
     * @param string $name
     * @return mixed
     */
    static public function create(ContainerInterface $container, string $name);
}