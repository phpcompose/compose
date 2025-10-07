<?php

namespace Compose\Event;

use Compose\Container\ResolvableInterface;
use Psr\EventDispatcher\ListenerProviderInterface;

class ListenerProvider implements ListenerProviderInterface, ResolvableInterface
{
    /** @var array<class-string, list<callable>> */
    private array $listeners = [];

    public function addListener(string $eventIdentifier, callable $listener): void
    {
        $this->listeners[$eventIdentifier][] = $listener;
    }

    public function addSubscriber(SubscriberInterface $subscriber): void
    {
        foreach ($subscriber->getSubscribedEvents() as $eventIdentifier => $callable) {
            $this->addListener($eventIdentifier, $callable);
        }
    }

    public function getListenersForEvent(object $event): iterable
    {
        $listeners = [];
        if ($event instanceof EventInterface && isset($this->listeners[$event->identifier()])) {
            $listeners = $this->listeners[$event->identifier()];
        } else {
            foreach ($this->listeners as $eventIdentifier => $eventListeners) {
                if (is_a($event, $eventIdentifier)) {
                    foreach ($eventListeners as $listener) {
                        $listeners[] = $listener;
                    }
                }
            }
        }
        foreach ($listeners as $listener) {
            yield $listener;
        }
    }
}
