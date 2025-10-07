<?php

namespace Compose\Http\Event;

use Psr\EventDispatcher\StoppableEventInterface;
use Throwable;

final class ExceptionEvent implements StoppableEventInterface
{
    private bool $propagationStopped = false;

    public function __construct(private Throwable $throwable)
    {
    }

    public function throwable(): Throwable
    {
        return $this->throwable;
    }

    public function stopPropagation(): void
    {
        $this->propagationStopped = true;
    }

    public function isPropagationStopped(): bool
    {
        return $this->propagationStopped;
    }
}
