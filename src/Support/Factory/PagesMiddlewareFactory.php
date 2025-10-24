<?php

namespace Compose\Support\Factory;

use Compose\Container\ServiceFactoryInterface;
use Compose\Pages\PagesMiddleware;
use Compose\Support\Configuration;
use Compose\Template\TemplateRenderer;
use Laminas\Stratigility\Middleware\PathMiddlewareDecorator;
use Psr\Container\ContainerInterface;
use Psr\Http\Server\MiddlewareInterface;

final class PagesMiddlewareFactory implements ServiceFactoryInterface
{
    public static function create(ContainerInterface $container, string $id): MiddlewareInterface
    {
        /** @var TemplateRenderer $renderer */
        $renderer = $container->get(TemplateRenderer::class);
        $middleware = new PagesMiddleware($renderer);
        $middleware->setContainer($container);

        /** @var Configuration $configuration */
        $configuration = $container->get(Configuration::class);
        $pagesConfig = $configuration['pages'] ?? [];

        if (isset($pagesConfig['dir'])) {
            $middleware->setDirectory($pagesConfig['dir'], $pagesConfig['namespace'] ?? null);
        }

        if (!empty($pagesConfig['folders']) && is_array($pagesConfig['folders'])) {
            $middleware->setFolders($pagesConfig['folders']);
        }

        $path = $pagesConfig['path'] ?? null;
        if (is_string($path)) {
            $trimmed = trim($path);
            if ($trimmed !== '' && $trimmed !== '/') {
                return new PathMiddlewareDecorator($trimmed, $middleware);
            }
        }

        return $middleware;
    }
}
