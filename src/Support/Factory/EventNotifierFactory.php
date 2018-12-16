<?php
/**
 * Created by PhpStorm.
 * User: alaminahmed
 * Date: 2018-03-27
 * Time: 2:13 PM
 */

namespace Compose\Support\Factory;


use Compose\Container\ServiceFactoryInterface;
use Compose\Event\EventNotifier;
use Compose\Support\Configuration;
use Psr\Container\ContainerInterface;

class EventNotifierFactory implements ServiceFactoryInterface
{
    static public function create(ContainerInterface $container, string $id)
    {
        // TODO: Implement create() method.
        $config = $container->get(Configuration::class);
        $notifier = new EventNotifier();
        $subscribers = $config['subscribers'];
        if($subscribers) {
            foreach($subscribers as $subscriber) {
                $notifier->subscribe($container->get($subscriber));
            }
        }

        return $notifier;
    }
}