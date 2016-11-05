<?php
/**
 * Created by PhpStorm.
 * User: alaminahmed
 * Date: 2016-11-04
 * Time: 6:59 PM
 */

namespace Compose\System\Event;


interface ListenerInterface
{
    public function onEvent(string $event, EventArgsInterface $args);
}