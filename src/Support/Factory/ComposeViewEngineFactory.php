<?php

namespace Compose\Support\Factory;

use Compose\Container\ServiceFactoryInterface;
use Compose\Container\ServiceResolver;
use Compose\Mvc\ComposeViewEngine;
use Compose\Mvc\Helper\HelperRegistry;
use Compose\Mvc\ViewEngineInterface;
use Compose\Support\Configuration;
use Psr\Container\ContainerInterface;

class ComposeViewEngineFactory implements ServiceFactoryInterface
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

        $helpers = $templates['helpers'] ?? ($configuration['helpers'] ?? []);

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

        return new ComposeViewEngine(array_merge($config, $templates), $registry);
    }
}
