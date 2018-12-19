<?php
/**
 * Created by PhpStorm.
 * User: alaminahmed
 * Date: 2017-12-04
 * Time: 1:42 PM
 */

namespace Compose\Event;
use Psr\EventDispatcher\EventInterface;
use Psr\EventDispatcher\MessageInterface;


/**
 * Class EventNotifier
 * @package Compose\Event
 */
class EventDispatcher implements EventDispatcherInterface
{
    protected
        /**
         * @var array
         */
        $listeners = [];

    /**
     * @inheritdoc
     * @param EventInterface $event
     * @return iterable
     */
    public function getListenersForEvent(EventInterface $event): iterable
    {
        $name = null;
        if($event instanceof Message) {
            $name = $event->getName();
        } else {
            $name = get_class($event);
        }

        return $this->listeners[$name] ?? [];
    }

    /**
     * @inheritdoc
     * @param string $event
     * @param callable $callback
     * @param int $priority
     * @return bool
     */
    public function attach(string $event, callable $listener): void
    {
        if(!isset($this->listeners[$event])) {
            $this->listeners[$event] = [];
        }

        $this->listeners[$event][] = $listener;
    }

    /**
     * @inheritdoc
     * @param $event
     * @param callable $callback
     */
    public function detach(string $event, callable $listener) : void
    {
        $listeners = $this->listeners[$event] ?? null;
        if(!$listeners) return;

        foreach($listeners as $index => $aListener) {
            if($aListener !== $listener) continue;
            unset($this->listeners[$event][$index]);
        }
    }

    /**
     * @param callable $callable
     */
    public function subscribe(SubscriberInterface $subscriber)
    {
        $events = $subscriber->subscribedEvents();
        foreach($events as $event => $methods) {
            if(is_array($methods)) {
                foreach($methods as $method) {
                    $this->attach($event, [$subscriber, $method]);
                }
            } else {
                $this->attach($event, [$subscriber, $methods]);
            }
        }
    }

    /**
     * @param SubscriberInterface $subscriber
     */
    public function unsubscribe(SubscriberInterface $subscriber)
    {
        $events = $subscriber->subscribedEvents();
        foreach($events as $event => $methods) {
            if(is_array($methods)) {
                foreach($methods as $method) {
                    $this->detach($event, [$subscriber, $method]);
                }
            } else {
                $this->detach($event, [$subscriber, $methods]);
            }
        }
    }

    /**
     * @inheritdoc
     * @param MessageInterface $message
     * @throws \Exception
     */
    public function notify(MessageInterface $message) : void
    {
        $listeners = $this->getListenersForEvent($message);

        try {
            foreach($listeners as $listener) {
                $listener($message);
            }
        } catch (\Exception $e) {
            throw $e;
        }
    }
}