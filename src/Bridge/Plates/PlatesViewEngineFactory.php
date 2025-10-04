<?php

namespace Compose\Bridge\Plates;

use Compose\Container\ServiceFactoryInterface;
use Compose\Mvc\ViewEngineInterface;
use Compose\Support\Configuration;
use InvalidArgumentException;
use League\Plates\Engine;
use Psr\Container\ContainerInterface;

class PlatesViewEngineFactory implements ServiceFactoryInterface
{
    public static function create(ContainerInterface $container, string $id): ViewEngineInterface
    {
        $configuration = $container->get(Configuration::class);
        $templates = $configuration['templates'] ?? [];

        return self::createPlatesEngine($container, $templates, $configuration);
    }

    private static function createPlatesEngine(ContainerInterface $container, array $templates, Configuration|array $configuration): ViewEngineInterface
    {
        $directory = $templates['dir'] ?? COMPOSE_DIR_TEMPLATE;
        $folders = $templates['folders'] ?? [];
        $extension = $templates['extension'] ?? 'phtml';
        $defaultLayout = $templates['layout'] ?? null;
        $helpers = $templates['helpers'] ?? ($configuration['helpers'] ?? []);

        $engine = new Engine($directory);
        $engine->setFileExtension($extension);

        foreach ($folders as $name => $path) {
            $engine->addFolder($name, $path, true);
        }

        $viewEngine = new PlatesViewEngine($engine, $defaultLayout);

        foreach (self::normalizeHelpers($helpers) as $alias => $definition) {
            $callable = self::resolveHelperCallable($definition, $container);
            $viewEngine->registerHelper($alias, $callable);
        }

        return $viewEngine;
    }

    private static function resolveHelperCallable($definition, ContainerInterface $container): callable
    {
        if (is_string($definition) && class_exists($definition)) {
            $instance = $container->get($definition);
            if (!is_callable($instance)) {
                throw new InvalidArgumentException(sprintf('Helper "%s" must be invokable.', $definition));
            }
            return $instance;
        }

        if (is_callable($definition)) {
            return $definition;
        }

        throw new InvalidArgumentException('Helper definition must be a callable or invokable class name.');
    }

    /**
     * @param array<int|string, mixed> $helpers
     * @return array<string, mixed>
     */
    private static function normalizeHelpers(array $helpers): array
    {
        $normalized = [];

        foreach ($helpers as $alias => $definition) {
            $name = is_int($alias) ? self::deriveAlias($definition) : (string) $alias;

            if ($name === '') {
                throw new InvalidArgumentException('Helper alias must be a non-empty string.');
            }

            $normalized[$name] = $definition;
        }

        return $normalized;
    }

    private static function deriveAlias($definition): string
    {
        if (is_string($definition)) {
            $segments = explode('\\', $definition);
            $short = end($segments);
            $short = preg_replace('/Helper$/', '', $short) ?: $short;
            return strtolower($short);
        }

        if ($definition instanceof \Closure) {
            return 'helper_' . spl_object_id($definition);
        }

        return 'helper_' . spl_object_id((object) $definition);
    }
}
