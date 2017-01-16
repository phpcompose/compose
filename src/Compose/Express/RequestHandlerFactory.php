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

        if(isset($config['routes'])) {
            $handlers = $config['routes'];

            foreach($handlers as $path => $command) {
                $handler->route($path, $command);
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
