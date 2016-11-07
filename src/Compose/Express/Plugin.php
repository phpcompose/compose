<?php
/**
 * Created by PhpStorm.
 * User: alaminahmed
 * Date: 2016-11-05
 * Time: 4:52 PM
 */

namespace Compose\Express;


use Compose\System\Event\EventInterface;
use Compose\System\Event\NotifierInterface;

abstract class Plugin
{
    /**
     * @param NotifierInterface $notifier
     */
    public function onPlug(NotifierInterface $notifier)
    {
        $events = $this->getPluginEvents();
        foreach($events as $event) {
            $notifier->attach($event, $this);
        }
    }

    /**
     * @param NotifierInterface $notifier
     */
    public function onUnplug(NotifierInterface $notifier)
    {
        $events = $this->getPluginEvents();
        foreach($events as $event) {
            $notifier->detach($event, $this);
        }
    }

    /**
     * @param EventInterface $event
     * @return mixed
     * @throws \Exception
     */
    public function __invoke(EventInterface $event)
    {
        $name = $event->getName();
        $method = 'on' . ucfirst($name);

        if(!method_exists($this, $method)) {
            throw new \Exception("Method ({$method}) not provided for the Event ({$event})");
        }

        return $this->$method($event);
    }

    /**
     * @return array
     */
    abstract public function getPluginEvents(): array;
}