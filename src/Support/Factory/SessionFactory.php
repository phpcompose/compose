<?php
/**
 * Created by PhpStorm.
 * User: alaminahmed
 * Date: 2018-01-11
 * Time: 9:25 AM
 */

namespace Compose\Support\Factory;


use Compose\Container\ServiceFactoryInterface;
use Compose\Http\Session;
use Compose\Support\Configuration;
use Psr\Container\ContainerInterface;

/**
 * Class SessionFactory
 * @package Compose\Support\Factory
 */
class SessionFactory implements ServiceFactoryInterface
{
    /**
     * @param ContainerInterface $container
     * @return Session
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    static public function create(ContainerInterface $container, string $id)
    {
        $configuration = $container->get(Configuration::class);

        if($container->has(\SessionHandlerInterface::class)) {
            $handler = $container->get(\SessionHandlerInterface::class);
        } else {
            $handler = null;
        }

        $config = $configuration['session'] ?? null;
        $session = new Session($config);
        $session->start($handler);

        return $session;
    }
}