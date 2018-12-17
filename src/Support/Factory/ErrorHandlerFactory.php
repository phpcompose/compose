<?php
/**
 * Created by PhpStorm.
 * User: alaminahmed
 * Date: 2018-03-27
 * Time: 2:07 PM
 */

namespace Compose\Support\Factory;


use Compose\Container\ServiceFactoryInterface;
use Compose\Container\ZendFactoryMapTrait;
use Compose\Mvc\ViewRenderer;
use Compose\Mvc\ViewRendererInterface;
use Compose\Support\Configuration;
use Compose\Support\Error\ErrorResponseGenerator;
use Psr\Container\ContainerInterface;
use Zend\Diactoros\Response;
use Zend\Stratigility\Middleware\ErrorHandler;

class ErrorHandlerFactory implements ServiceFactoryInterface
{
    use ZendFactoryMapTrait;

    static public function create(ContainerInterface $container, string $id)
    {
        $config = $container->get(Configuration::class);
        $generator = new ErrorResponseGenerator($container->get(ViewRendererInterface::class), $container->get(Configuration::class));

        // error handler
        $errorHandler = new ErrorHandler(
            function() { return new Response(); },
            $generator
        );

        $errorListeners = $config['error_listeners'] ?? [];
        foreach($errorListeners as $errorListener) {
            $errorHandler->attachListener($container->get($errorListener));
        }

        return $errorHandler;
    }
}