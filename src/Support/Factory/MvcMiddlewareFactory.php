<?php
/**
 * Created by PhpStorm.
 * User: alaminahmed
 * Date: 2018-06-24
 * Time: 10:55 AM
 */

namespace Compose\Support\Factory;


use Compose\Container\ServiceFactoryInterface;
use Compose\Routing\DispatchMiddleware;
use Compose\Mvc\MvcMiddleware;
use Compose\Mvc\PagesMiddleware;
use Compose\Routing\Route;
use Compose\Routing\RoutingMiddleware;
use Compose\Support\Configuration;
use Psr\Container\ContainerInterface;

class MvcMiddlewareFactory implements ServiceFactoryInterface
{
    public static function create(ContainerInterface $container, string $name): MvcMiddleware
    {
        $config = $container->get(Configuration::class);
        $mvc = new MvcMiddleware();
        $mvc->setContainer($container);

        /** @var RoutingMiddleware $routing */
        $routing = $container->get(RoutingMiddleware::class);
        $routing->setContainer($container);
        $routes = $config['routes'] ?? [];
        if ($routes) {
            foreach ($routes as $path => $command) {
                $routing->route(Route::fromArray([
                    'path' => $path,
                    'handler' => $command
                ]));
            }
        }

        $mvc->pipe($routing);

        /** @var DispatchMiddleware $dispatcher */
        $dispatcher = $container->get(DispatchMiddleware::class);
        $dispatcher->setContainer($container);
        $mvc->pipe($dispatcher);

        /** @var PagesMiddleware $pageHandler */
        $pageHandler = $container->get(PagesMiddleware::class);
        $pageHandler->setContainer($container);
        $pages = $config['pages'] ?? [];
        $pageDir = $pages['dir'] ?? null;
        if ($pageDir) {
            $pageHandler->setDirectory($pageDir, $pages['namespace'] ?? null);
        }

        $folders = $pages['folders'] ?? null;
        if ($folders) {
            $pageHandler->setFolders($folders);
        }
        $mvc->pipe(middleware: $pageHandler);

        return $mvc;
    }
}
