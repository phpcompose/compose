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
use InvalidArgumentException;
use Psr\Container\ContainerInterface;

class HelperRegistryFactory implements ServiceFactoryInterface
{
    public static function create(ContainerInterface $container, string $class)
    {
        $configuration = $container->get(Configuration::class);
        $definitions  = $configuration['helpers'] ?? [];

        $registry = new HelperRegistry($container->get(ServiceResolver::class));

        foreach ($definitions as $name => $definition) {
            if (is_int($name)) {
                if (!is_string($definition)) {
                    throw new InvalidArgumentException('Helper definitions without explicit names must be class strings.');
                }

                $name = self::deriveAlias($definition);
            }

            $registry->register($name, $definition);

            if (is_string($definition)) {
                self::registerHelperMethods($registry, $name, $definition);
            }
        }

        return $registry;
    }

    private static function deriveAlias(string $class): string
    {
        $segments = explode('\\', $class);
        $short = end($segments);
        $short = preg_replace('/Helper$/', '', $short) ?: $short;

        return strtolower($short);
    }

    private static function registerHelperMethods(HelperRegistry $registry, string $alias, string $class): void
    {
        $reflection = new \ReflectionClass($class);

        foreach ($reflection->getMethods(\ReflectionMethod::IS_PUBLIC) as $method) {
            $name = $method->getName();

            if ($name === '__construct' || $name === '__invoke' || str_starts_with($name, '__')) {
                continue;
            }

            $registry->registerMethodAlias($name, $alias, $name);
        }
    }
}
