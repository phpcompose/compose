<?php
/**
 * Created by PhpStorm.
 * User: alaminahmed
 * Date: 2018-03-27
 * Time: 2:07 PM
 */

namespace Compose\Support\Factory;


use Compose\Container\ServiceFactoryInterface;
use Compose\Mvc\ViewEngineInterface;
use Compose\Support\Configuration;
use Compose\Support\Error\ErrorResponseGenerator;
use Psr\Container\ContainerInterface;
use Laminas\Diactoros\ResponseFactory;
use Laminas\Stratigility\Middleware\ErrorHandler;

class ErrorHandlerFactory implements ServiceFactoryInterface
{
    static public function create(ContainerInterface $container, string $id)
    {
        $config = $container->get(Configuration::class);
        $generator = new ErrorResponseGenerator(
            $container->get(ViewEngineInterface::class),
            $config
        );

        // error handler
        $errorHandler = new ErrorHandler(new ResponseFactory(), $generator);

        $errorListeners = $config['error_listeners'] ?? [];
        foreach($errorListeners as $errorListener) {
            $errorHandler->attachListener($container->get($errorListener));
        }

        return $errorHandler;
    }
}
