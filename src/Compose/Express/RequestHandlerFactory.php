<?php
/**
 * Created by PhpStorm.
 * User: alaminahmed
 */

namespace Compose\Express;


use Interop\Container\ContainerInterface;

/**
 * Class RequestHandlerFactory
 * @package Compose\Express
 */
class RequestHandlerFactory
{
    /**
     * @param ContainerInterface $container
     * @return RequestHandler
     */
    public function __invoke(ContainerInterface $container)
    {
        $config = ($container->has('config')) ? $container->get('config') : [];
        $handler = new RequestHandler();

        if(isset($config['paths'])) {
            $handlers = $config['paths'];

            foreach($handlers as $path => $class) {
                if(is_string($class)) {
                    $callable = $container->get($class);
                } else {
                    $callable = $class;
                }

                $handler->pipe($path, $callable);
            }
        }

        return $handler;
    }

    /**
     * @param ContainerInterface $container
     * @return mixed
     */
    public static function create(ContainerInterface $container)
    {
        $factory = new self();
        return $factory($container);
    }
}
