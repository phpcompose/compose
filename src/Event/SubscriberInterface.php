<?php
/**
 * Created by PhpStorm.
 * User: alaminahmed
 * Date: 2017-12-31
 * Time: 8:59 PM
 */

namespace Compose\Event;

/**
 * Interface SubscriberInterface
 * @package Compose\Event
 */
interface SubscriberInterface
{
    /**
     * Subscribed events.
     * @return array
     */
    public function subscribedEvents() : array;
}