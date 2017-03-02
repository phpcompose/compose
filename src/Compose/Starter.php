<?php

/**
 * Created by PhpStorm.
 * User: Alamin
 */

namespace Compose;


use Compose\Support\Error\ErrorResponseGenerator;
use Compose\Support\Error\NotFoundMiddleware;
use Psr\Container\ContainerInterface;
use Zend\Diactoros\Response;
use Zend\Diactoros\Server;
use Zend\Diactoros\ServerRequestFactory;
use Zend\Stratigility\Middleware\ErrorHandler;
use Zend\Stratigility\Middleware\OriginalMessages;
use Zend\Stratigility\NoopFinalHandler;

define('COMPOSE_DIR', dirname(dirname(__FILE__)));
define('COMPOSE_DIR_TEMPLATE', COMPOSE_DIR . '/../templates');

/**
 * Class Starter
 *
 * Helper class to bootstart and start Compose application
 * @package Compose
 */
class Starter
{
    /**
     * @param ContainerInterface $container
     * @param \Closure|null $readyCallback
     */
    public function __invoke(ContainerInterface $container, \Closure $readyCallback = null)
    {
        $response = new Response(); // response prototype

        // create and setup the pipeline
        $app = new Application($container);
        $app->setResponsePrototype($response);
        $app->pipe(new OriginalMessages());
        $app->pipe(new ErrorHandler($response, $container->get(ErrorResponseGenerator::class)));

        // application ready callback
        if($readyCallback) {
            $readyCallback($app, $container);
        }

        // final/not found handler
        $app->pipe($container->get(NotFoundMiddleware::class));

        // create and start the server
        $server = Server::createServerFromRequest(
            $app,
            ServerRequestFactory::fromGlobals()
        );
        $server->listen(new NoopFinalHandler());
    }

    /**
     * @param ContainerInterface $container
     * @param \Closure|null $ready
     */
    static public function start(ContainerInterface $container, \Closure $ready = null)
    {
        $starter = new static();
        $starter($container, $ready);
    }
}