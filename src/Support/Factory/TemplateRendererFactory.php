<?php

namespace Compose\Support\Factory;

use Compose\Container\ServiceFactoryInterface;
use Compose\Container\ServiceResolver;
use Compose\Support\Configuration;
use Compose\Template\Helper\HelperRegistry;
use Compose\Template\RendererInterface;
use Compose\Template\TemplateRenderer;
use Psr\Container\ContainerInterface;

class TemplateRendererFactory implements ServiceFactoryInterface
{
    public static function create(ContainerInterface $container, string $id): RendererInterface
    {
        $configuration = $container->get(Configuration::class);
        $templateConfig = $configuration['template'] ?? $configuration['templates'] ?? [];

        return self::createComposeEngine($container, $templateConfig, $configuration);
    }

    private static function createComposeEngine(ContainerInterface $container, array $templateConfig, Configuration|array $configuration): RendererInterface
    {
        $resolver = $container->get(ServiceResolver::class);
        $registry = new HelperRegistry($resolver);

        $helpers = $templateConfig['helpers'] ?? [];

        foreach ($helpers as $alias => $definition) {
            if (is_int($alias)) {
                $registry->extend($definition);
                continue;
            }

            $registry->register((string) $alias, $definition);
        }

        $config = [
            'dir' => $templateConfig['dir'] ?? COMPOSE_DIR_TEMPLATE,
            'folders' => $templateConfig['folders'] ?? [],
            'maps' => $templateConfig['maps'] ?? [],
            'layout' => $templateConfig['layout'] ?? null,
            'extension' => $templateConfig['extension'] ?? 'phtml',
        ];

        return new TemplateRenderer(array_merge($config, $templateConfig), $registry);
    }
}
