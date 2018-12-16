<?php
/**
 * Created by PhpStorm.
 * User: alaminahmed
 * Date: 2017-11-28
 * Time: 8:43 AM
 */

namespace Compose\Mvc;

/**
 * Class CommandRoute
 *
 * Holds routing information for command
 * @package Compose\Http
 */
class RouteInfo extends \ArrayObject
{
    public
        /**
         * @var string
         */
        $name,

        /**
         * @var string
         */
        $path,

        /**
         * @var array
         */
        $params = [],

        /**
         * @var string
         */
        $method,

        /**
         * @var mixed
         */
        $handler;


    /**
     * @param array $arr
     * @return static
     */
    public static function fromArray(array $arr)
    {
        $map = new static();
        $map->method = $arr['method'] ?? null;
        $map->path = $arr['path'] ?? null;
        $map->name = $arr['name'] ?? null;
        $map->params = $arr['params'] ?? [];
        $map->handler = $arr['handler'] ?? null;

        return $map;
    }
}