<?php
/**
 * Created by PhpStorm.
 * User: alaminahmed
 * Date: 2016-11-02
 * Time: 9:28 PM
 */

namespace Compose\System\Event;


interface EventArgsInterface
{
    public function getSubject();
    public function getArguments() : array;
}