<?php
/**
 * Created by PhpStorm.
 * User: alaminahmed
 * Date: 2016-11-02
 * Time: 9:27 PM
 */

namespace Compose\System\Event;


interface NotifierInterface
{
    public function notify(string $event, EventArgsInterface $args);
    public function addListener(string $event, ListenerInterface $listener);
    public function removeListener(string $event, ListenerInterface $listener);
}