<?php
/**
 * Created by PhpStorm.
 * User: alaminahmed
 * Date: 2017-12-04
 * Time: 1:25 PM
 */

namespace Compose\Event;


/**
 * Interface EventManagerInterface
 *
 * @note
 *  - removing a listener only with callback is expensive task.  And rare.
 *      therefore detach function takes both $event name and the callback.  This is how NodeJS and Symfony anothers do
 *
 * @package Compose\Event
 */
interface EventDispatcherInterface
{
    /**
     * @param string $event
     * @param callable $callback
     * @param int $priority
     * @return mixed
     */
    public function attach(string $event, callable $callback) : void;

    /**
     * @param string $event
     * @param callable $callback
     * @return mixed
     */
    public function detach(string $event, callable $callback): void;

    /**
     * @param SubscriberInterface $subscriber
     * @return mixed
     */
    public function subscribe(SubscriberInterface $subscriber);

    /**
     * @param SubscriberInterface $subscriber
     * @return mixed
     */
    public function unsubscribe(SubscriberInterface $subscriber);

    /**
     * @param EventInterface $event
     * @return mixed
     */
    public function dispatch(EventInterface $event);


    /**
     * @param EventInterface $event
     * @return iterable
     */
    public function getListenersForEvent(EventInterface $event) : iterable;
}