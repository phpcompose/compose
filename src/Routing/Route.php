<?php
namespace Compose\Routing;

class Route extends \ArrayObject
{
    public
        $name,
        $path,
        $params = [],
        $method,
        $handler;

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
