<?php
/**
 * Created by PhpStorm.
 * User: alaminahmed
 * Date: 2018-06-24
 * Time: 10:55 AM
 */

namespace Compose\Support\Factory;


use Compose\Container\ServiceFactoryInterface;
use Compose\Mvc\DispatchingMiddleware;
use Compose\Mvc\MvcMiddleware;
use Compose\Mvc\PagesHandler;
use Compose\Mvc\RouteInfo;
use Compose\Mvc\RoutingMiddleware;
use Compose\Support\Configuration;
use Psr\Container\ContainerInterface;

class MvcMiddlewareFactory implements ServiceFactoryInterface
{
    public static function create(ContainerInterface $container, string $name)
    {
        $config = $container->get(Configuration::class);
        $mvc = new MvcMiddleware();
        $mvc->setContainer($container);

        /** @var PagesHandler $pageHandler */
        $pageHandler = $container->get(PagesHandler::class);
        $pages = $config['pages'] ?? [];
        $pageDir = $pages['dir'] ?? null;
        if($pageDir) {
            $pageHandler->setDirectory($pageDir);
        }
        $mvc->pipe($pageHandler);

        /** @var RoutingMiddleware $routing */
        $routing = $container->get(RoutingMiddleware::class);
        $routes = $config['routes'] ?? [];
        if($routes) {
            foreach($routes as $path => $command) {
                $routing->route(RouteInfo::fromArray([
                    'path' => $path,
                    'handler' => $command
                ]));
            }
        }

        $mvc->pipe($routing);
        $mvc->pipe($container->get(DispatchingMiddleware::class));

        return $mvc;
    }


    public function __invoke(ContainerInterface $container, $id)
    {
        return self::create($container, $id);
    }
}