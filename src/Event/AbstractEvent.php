<?php

namespace Compose\Event;

abstract class AbstractEvent implements EventInterface
{
    public function identifier(): string
    {
        return static::class;
    }
}
