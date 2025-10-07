<?php

namespace Compose\Support\Factory;

use Compose\Container\ServiceFactoryInterface;
use Compose\Event\ListenerProvider;
use Compose\Event\SubscriberInterface;
use Compose\Support\Configuration;
use Psr\Container\ContainerInterface;
use Psr\EventDispatcher\ListenerProviderInterface;

/**
 * Instanciates the ListenerProvider also registers subscribers defined in the config file
 */
final class ListenerProviderFactory implements ServiceFactoryInterface
{
    public static function create(ContainerInterface $container, string $id): ListenerProviderInterface
    {
        $configuration = $container->get(Configuration::class);
        $provider = new ListenerProvider();

        foreach ($configuration['subscribers'] ?? [] as $subscriberId) {
            /** @var SubscriberInterface $subscriber */
            $subscriber = $container->get($subscriberId);
            $provider->addSubscriber($subscriber);
        }

        return $provider;
    }
}
