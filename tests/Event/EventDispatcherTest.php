<?php

declare(strict_types=1);

namespace Compose\Event;

use Compose\Event\EventDispatcher;
use Compose\Event\ListenerProvider;
use Compose\Event\SubscriberInterface;
use PHPUnit\Framework\TestCase;
use Psr\EventDispatcher\EventDispatcherInterface;

final class EventDispatcherTest extends TestCase
{
    private EventDispatcherInterface $dispatcher;
    private ListenerProvider $provider;

    protected function setUp(): void
    {
        $this->provider = new ListenerProvider();
        $this->dispatcher = new EventDispatcher($this->provider);
    }

    public function testListenerReceivesEvent(): void
    {
        $event = new class {
            public bool $handled = false;
        };

        $this->provider->addListener($event::class, function ($e) {
            $e->handled = true;
        });

        $this->dispatcher->dispatch($event);

        $this->assertTrue($event->handled);
    }

    public function testSubscriberRegistersListeners(): void
    {
        $event = new class {
            public int $count = 0;
        };

        $subscriber = new class($event) implements SubscriberInterface {
            public function __construct(private object $event) {}

            public function getSubscribedEvents(): array
            {
                return [get_class($this->event) => 'onEvent'];
            }

            public function onEvent(object $event): void
            {
                $event->count++;
            }
        };

        $this->provider->addSubscriber($subscriber);

        $this->dispatcher->dispatch($event);

        $this->assertSame(1, $event->count);
    }

    public function testStoppableEventStopsPropagation(): void
    {
        $event = new class implements \Psr\EventDispatcher\StoppableEventInterface {
            public int $count = 0;
            private bool $stopped = false;

            public function stop(): void
            {
                $this->stopped = true;
            }

            public function isPropagationStopped(): bool
            {
                return $this->stopped;
            }
        };

        $this->provider->addListener($event::class, function ($e) {
            $e->count++;
            $e->stop();
        });

        $this->provider->addListener($event::class, function ($e) {
            $e->count++;
        });

        $this->dispatcher->dispatch($event);

        $this->assertSame(1, $event->count);
    }
}
