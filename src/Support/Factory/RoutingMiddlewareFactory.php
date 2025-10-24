<?php

namespace Compose\Support\Factory;

use Compose\Container\ServiceFactoryInterface;
use Compose\Routing\Route;
use Compose\Routing\RoutingMiddleware;
use Compose\Support\Configuration;
use Psr\Container\ContainerInterface;

final class RoutingMiddlewareFactory implements ServiceFactoryInterface
{
    public static function create(ContainerInterface $container, string $id): RoutingMiddleware
    {
        $middleware = new RoutingMiddleware();
        $middleware->setContainer($container);

        /** @var Configuration $configuration */
        $configuration = $container->get(Configuration::class);
        $routes = $configuration['routes'] ?? [];

        foreach ($routes as $path => $handler) {
            $middleware->route(Route::fromArray([
                'path' => $path,
                'handler' => $handler,
            ]));
        }

        return $middleware;
    }
}
