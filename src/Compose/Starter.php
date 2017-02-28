<?php

/**
 * Created by PhpStorm.
 * User: Alamin
 */

namespace Compose;


use Compose\Adapter\Zend\ServiceContainerFactory;
use Compose\Support\ContainerFactory;
use Compose\Support\Error\NotFoundMiddleware;
use Interop\Container\ContainerInterface;
use Zend\Diactoros\Server;
use Zend\Diactoros\ServerRequestFactory;
use Zend\Stratigility\Middleware\ErrorHandler;
use Zend\Stratigility\MiddlewarePipe;

define('COMPOSE_DIR', dirname(dirname(__FILE__)));
define('COMPOSE_DIR_TEMPLATE', COMPOSE_DIR . '/../templates');


class Loader
{
    const
        CONFIG_KEY_SERVICES = 'services';

    public function __construct()
    {
    }


    /**
     * @param array $config
     */
    public function load(array $config)
    {
        $container = $this->loadContainer($config);
        $pipeline = $this->loadPipeline($container);
    }

    /**
     * @param array $config
     * @return \Interop\Container\ContainerInterface
     */
    protected function loadContainer(array $config)
    {
        $serviceContainer = ServiceContainerFactory::createFromConfig($config[CONFIG_KEY_SERVICES] ?? []);
        return ContainerFactory::createFromConfig($config, $serviceContainer);
    }

    /**
     * @param ContainerInterface $container
     * @return MiddlewarePipe
     */
    protected function loadPipeline(ContainerInterface $container)
    {
        $pipeline = new MiddlewarePipe();
        $pipeline->pipe($container->get(ErrorHandler::class));

        return $pipeline;
    }

    /**
     * @param MiddlewarePipe $pipe
     */
    static public function start(MiddlewarePipe $pipe) : void
    {
        $server = Server::createServerFromRequest(
            $pipe,
            ServerRequestFactory::fromGlobals()
        );
        $server->listen();
    }
}