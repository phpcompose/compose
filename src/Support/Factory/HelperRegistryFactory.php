<?php
/**
 * Created by PhpStorm.
 * User: alaminahmed
 * Date: 2018-01-09
 * Time: 12:55 PM
 */

namespace Compose\Support\Factory;


use Compose\Container\ServiceFactoryInterface;
use Compose\Container\ServiceResolver;
use Compose\Mvc\Helper\HelperRegistry;
use Compose\Support\Configuration;
use Psr\Container\ContainerInterface;

class HelperRegistryFactory implements ServiceFactoryInterface
{
    /**
     * @param ContainerInterface $container
     * @param string $class
     * @return HelperRegistry
     * @throws \ReflectionException
     */
    static public function create(ContainerInterface $container, string $class)
    {
        $configuration = $container->get(Configuration::class);
        $helpers  = $configuration['helpers'] ?? [];

        $registry = new HelperRegistry($container->get(ServiceResolver::class));
        foreach($helpers as $key => $val) {
            if(is_int($key)) {
                $registry->extend($val);
            } else {
                $registry->register($key, $val);
            }
        }

        return $registry;
    }


    public function __invoke(ContainerInterface $container, $id)
    {
        return self::create($container, $id);
    }
}