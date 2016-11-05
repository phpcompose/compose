<?php
/**
 * Created by PhpStorm.
 * User: alaminahmed
 * Date: 2016-11-04
 * Time: 11:39 PM
 */

namespace Compose\System\Event;


class CallableListener implements ListenerInterface
{
    protected
        $callable;

    /**
     * CallableListener constructor.
     * @param callable $callable
     */
    public function __construct(callable $callable)
    {
        $this->callable = $callable;
    }

    /**
     * @param string $event
     * @param EventArgsInterface $args
     * @return mixed
     */
    public function onEvent(string $event, EventArgsInterface $args)
    {
        return call_user_func($this->callable, $event, $args);
    }
}