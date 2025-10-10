<?php
/**
 * Created by PhpStorm.
 * User: alaminahmed
 * Date: 2018-01-11
 * Time: 9:25 AM
 */

namespace Compose\Support\Factory;
use Compose\Container\ServiceFactoryInterface;
use Compose\Http\Session\Session;
use Compose\Http\Session\NativeSessionStorage;
use Compose\Http\Session\SessionStorageInterface;
use Compose\Support\Configuration;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use SessionHandlerInterface;

/**
 * Class SessionFactory
 * @package Compose\Support\Factory
 */
class SessionFactory implements ServiceFactoryInterface
{
    /**
     * @param ContainerInterface $container
     * @return Session
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public static function create(ContainerInterface $container, string $id): Session
    {
        $configuration = $container->get(Configuration::class);

        if($container->has(SessionHandlerInterface::class)) {
            $handler = $container->get(SessionHandlerInterface::class);
        } else {
            $handler = null;
        }

        $config = $configuration['session'] ?? null;

        if ($container->has(SessionStorageInterface::class)) {
            $storage = $container->get(SessionStorageInterface::class);
        } else {
            $storage = new NativeSessionStorage();
        }

        $session = new Session($storage, $config);
        $session->start($handler);

        return $session;
    }
}
