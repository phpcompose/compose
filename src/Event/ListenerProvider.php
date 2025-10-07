<?php

namespace Compose\Event;

use Compose\Container\ResolvableInterface;
use Psr\EventDispatcher\ListenerProviderInterface;

class ListenerProvider implements ListenerProviderInterface, ResolvableInterface
{
    /** @var array<class-string, list<callable>> */
    private array $listeners = [];

    public function addListener(string $eventClass, callable $listener): void
    {
        $this->listeners[$eventClass][] = $listener;
    }

    public function addSubscriber(SubscriberInterface $subscriber): void
    {
        foreach ($subscriber->getSubscribedEvents() as $eventClass => $methods) {
            if (is_array($methods)) {
                foreach ($methods as $method) {
                    $this->addListener($eventClass, [$subscriber, $method]);
                }
                continue;
            }

            $this->addListener($eventClass, [$subscriber, $methods]);
        }
    }

    public function getListenersForEvent(object $event): iterable
    {
        foreach ($this->listeners as $eventClass => $listeners) {
            if (!is_a($event, $eventClass)) {
                continue;
            }

            foreach ($listeners as $listener) {
                yield $listener;
            }
        }
    }
}
