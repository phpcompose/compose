<?php
/**
 * Created by PhpStorm.
 * User: alaminahmed
 * Date: 2016-11-02
 * Time: 9:27 PM
 */

namespace Compose\System\Event;


use SplStack;

/**
 * Interface EventManagerInterface
 *
 * EventManagerInterface is interface per development of PSR-14
 * @package Compose\System\Event
 */
interface EventManagerInterface
{
    /**
     * @param string $event
     * @param callable $callback
     * @param int $priority
     * @return mixed
     */
    public function attach(string $event, callable $callback, int $priority = 0) : bool;

    /**
     * @param string $event
     * @param callable $callback
     * @return mixed
     */
    public function detach(string $event, callable $callback): bool;

    /**
     * @param string $event
     * @param null $target
     * @param array $args
     * @return mixed
     */
    public function trigger(string $event, $target = null, array $args = []): SplStack;
}