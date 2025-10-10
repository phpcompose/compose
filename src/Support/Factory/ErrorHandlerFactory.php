<?php
/**
 * Created by PhpStorm.
 * User: alaminahmed
 * Date: 2018-03-27
 * Time: 2:07 PM
 */

namespace Compose\Support\Factory;


use Compose\Container\ServiceFactoryInterface;
use Compose\Support\Configuration;
use Compose\Support\Error\ErrorResponseGenerator;
use Compose\Template\RendererInterface;
use InvalidArgumentException;
use Laminas\Diactoros\ResponseFactory;
use Laminas\Stratigility\Middleware\ErrorHandler;
use Psr\Container\ContainerInterface;

class ErrorHandlerFactory implements ServiceFactoryInterface
{
    public static function create(ContainerInterface $container, string $id): ErrorHandler
    {
        $config = $container->get(Configuration::class);
        $generator = new ErrorResponseGenerator(
            $container->get(RendererInterface::class),
            $config
        );

        // error handler
        $errorHandler = new ErrorHandler(new ResponseFactory(), $generator);

        $errorListeners = $config['error_listeners'] ?? [];
        foreach ($errorListeners as $errorListener) {
            // If a string is provided, treat it as a service id and fetch from container
            if (is_string($errorListener)) {
                $resolved = $container->get($errorListener);
            } else {
                $resolved = $errorListener;
            }

            // If it's an object with __invoke or a callable, attach directly
            if (is_object($resolved) && is_callable($resolved)) {
                $errorHandler->attachListener($resolved);
                continue;
            }

            if (is_callable($resolved)) {
                $errorHandler->attachListener($resolved);
                continue;
            }

            throw new InvalidArgumentException('Invalid error listener registered; must be a service id or callable');
        }

        return $errorHandler;
    }
}
