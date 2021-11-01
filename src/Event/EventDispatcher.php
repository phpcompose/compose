<?php
/**
 * Created by PhpStorm.
 * User: alaminahmed
 * Date: 2017-12-04
 * Time: 1:42 PM
 */

namespace Compose\Event;


use Exception;

/**
 * Class EventNotifier
 * @package Compose\Event
 */
class EventDispatcher implements EventDispatcherInterface
{
    protected array $listeners = [];

    /**
     * @param string $event
     * @return iterable
     */
    public function getListenersForEvent(string $event): iterable
    {
        return $this->listeners[$event] ?? [];
    }

    /**
     * @inheritdoc
     * @param string $event
     * @param callable $callback
     * @param int $priority
     * @return bool
     */
    public function attach(string $event, callable $callback): void
    {
        if(!isset($this->listeners[$event])) {
            $this->listeners[$event] = [];
        }

        $this->listeners[$event][] = $callback;
    }

    /**
     * @inheritdoc
     * @param $event
     * @param callable $callback
     */
    public function detach(string $event, callable $callback) : void
    {
        $listeners = $this->listeners[$event] ?? null;
        if(!$listeners) return;

        foreach($listeners as $index => $aListener) {
            if($aListener !== $callback) continue;
            unset($this->listeners[$event][$index]);
        }
    }

    /**
     * @param SubscriberInterface $subscriber
     */
    public function subscribe(SubscriberInterface $subscriber) : void
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
    public function unsubscribe(SubscriberInterface $subscriber) : void
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
     * @param string $event
     * @param MessageInterface $message
     * @throws Exception
     */
    public function dispatch(string $event, MessageInterface $message) : void
    {
        $listeners = $this->getListenersForEvent($event);

        foreach($listeners as $listener) {
            $listener($event, $message);
        }
    }
}