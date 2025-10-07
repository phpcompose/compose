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
        foreach ($subscriber->getSubscribedEvents() as $eventIdentifier => $listeners) {
            if (is_callable($listeners)) {
                $listeners = [$listeners];
            } else {
                $listeners = is_array($listeners) ? $listeners : [$listeners];
            }

            foreach ($listeners as $listener) {
                if (is_callable($listener)) {
                    $this->addListener($eventIdentifier, $listener);
                    continue;
                }

                if (is_string($listener)) {
                    $this->addListener($eventIdentifier, [$subscriber, $listener]);
                    continue;
                }

                if (is_array($listener) && isset($listener[0]) && is_string($listener[0])) {
                    // Support definitions like ['methodName', $priority]
                    $this->addListener($eventIdentifier, [$subscriber, $listener[0]]);
                    continue;
                }

                throw new \InvalidArgumentException('Invalid listener definition for event ' . $eventIdentifier);
            }
        }
    }

    public function getListenersForEvent(object $event): iterable
    {
        if ($event instanceof EventInterface) {
            $identifier = $event->identifier();
            if (isset($this->listeners[$identifier])) {
                yield from $this->listeners[$identifier];
            }
        }

        foreach ($this->listeners as $eventIdentifier => $eventListeners) {
            if ($event instanceof EventInterface && $eventIdentifier === $event->identifier()) {
                continue; // already yielded above
            }

            if (is_a($event, $eventIdentifier)) {
                yield from $eventListeners;
            }
        }
    }
}
