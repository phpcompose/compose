<?php

/**
 * Created by PhpStorm.
 * User: alaminahmed
 * Date: 2016-11-03
 * Time: 8:53 PM
 */

namespace Compose\Bridge\Symfony;


use Compose\System\Event\NotifierInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;

class EventNotifier implements NotifierInterface
{
    protected
        /**
         * keep listener hashed mapped.  This is needed if wanting to remove listner
         *
         * @var array
         */
        $listenerMap = [],
        $dispatcher;

    /**
     * EventNotifier constructor.
     * @param EventDispatcher|null $dispatcher
     */
    public function __construct(EventDispatcher $dispatcher = null)
    {
        if(!$dispatcher) $dispatcher = new EventDispatcher();

        $this->dispatcher = $dispatcher;
    }

    /**
     * @param string $event
     * @param null $target
     * @param array $args
     * @return \SplStack
     */
    public function notify(string $event, $target = null, array $args = []) : \SplStack
    {
        $this->dispatcher->dispatch($event, new Event($event, $target, $args));

        // Symfony event dispatcher does not support return types
        // so we will simply return an empty stack
        return new \SplStack();
    }


    /**
     * @param string $event
     * @param callable $callback
     * @param int $priority
     * @return bool
     */
    public function attach(string $event, callable $callback, int $priority = 0) : bool
    {
        $this->dispatcher->addListener($event, $callback, $priority);

        return true; //fake return
    }


    public function detach(string $event, callable $callback) : bool
    {
        $this->dispatcher->removeListener($event, $callback);

        return true; //fake return
    }
}