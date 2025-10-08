<?php

namespace Compose\Support\Factory;

use Compose\Container\ServiceFactoryInterface;
use Compose\Container\ServiceResolver;
use Compose\Mvc\ViewEngine;
use Compose\Mvc\Helper\HelperRegistry;
use Compose\Mvc\ViewEngineInterface;
use Compose\Support\Configuration;
use Psr\Container\ContainerInterface;

class ViewEngineFactory implements ServiceFactoryInterface
{
    public static function create(ContainerInterface $container, string $id): ViewEngineInterface
    {
        $configuration = $container->get(Configuration::class);
        $templates = $configuration['templates'] ?? [];

        return self::createComposeEngine($container, $templates, $configuration);
    }

    private static function createComposeEngine(ContainerInterface $container, array $templates, Configuration|array $configuration): ViewEngineInterface
    {
        $resolver = $container->get(ServiceResolver::class);
        $registry = new HelperRegistry($resolver);

        // Only use helpers declared under templates['helpers']. Root-level
        // 'helpers' support has been removed to keep view configuration
        // colocated under the templates key.
        $helpers = $templates['helpers'] ?? [];

        foreach ($helpers as $alias => $definition) {
            if (is_int($alias)) {
                $registry->extend($definition);
                continue;
            }

            $registry->register((string) $alias, $definition);
        }

        $config = [
            'dir' => $templates['dir'] ?? COMPOSE_DIR_TEMPLATE,
            'folders' => $templates['folders'] ?? [],
            'maps' => $templates['maps'] ?? [],
            'layout' => $templates['layout'] ?? null,
            'extension' => $templates['extension'] ?? 'phtml',
        ];

        return new ViewEngine(array_merge($config, $templates), $registry);
    }
}
