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
 *      therefore, detach function takes both $event name and the callback.  This is how NodeJS and Symfony another do
 *
 * @package Compose\Event
 */
interface EventDispatcherInterface
{
    /**
     * @param string $event
     * @param callable $callback
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
     * @return void
     */
    public function subscribe(SubscriberInterface $subscriber) : void;

    /**
     * @param SubscriberInterface $subscriber
     * @return void
     */
    public function unsubscribe(SubscriberInterface $subscriber) : void;

    /**
     * @param string $event
     * @param MessageInterface $message
     * @return void
     */
    public function dispatch(string $event, MessageInterface $message) : void;
}