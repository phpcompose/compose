<?php

namespace Compose\Support\Factory;

use Compose\Container\ServiceFactoryInterface;
use Compose\Container\ServiceResolver;
use Compose\Mvc\ComposeViewEngine;
use Compose\Mvc\Helper\FormatterHelper;
use Compose\Mvc\Helper\HelperRegistry;
use Compose\Mvc\Helper\LayoutHelper;
use Compose\Mvc\Helper\RequestHelper;
use Compose\Mvc\Helper\TagHelper;
use Compose\Mvc\ViewEngineInterface;
use Compose\Support\Configuration;
use InvalidArgumentException;
use Psr\Container\ContainerInterface;
use ReflectionClass;
use ReflectionMethod;

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

        foreach (self::normalizeHelpers($helpers) as $alias => $definition) {
            $registry->register($alias, $definition);

            if (is_string($definition) && class_exists($definition)) {
                self::registerHelperMethods($registry, $alias, $definition);
            }
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

    private static function registerHelperMethods(HelperRegistry $registry, string $alias, string $class): void
    {
        $reflection = new ReflectionClass($class);

        foreach ($reflection->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
            $name = $method->getName();
            if ($name === '__construct' || $name === '__invoke' || str_starts_with($name, '__')) {
                continue;
            }

            $registry->registerMethodAlias($name, $alias, $name);
        }
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
