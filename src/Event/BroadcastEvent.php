<?php

namespace Compose\Event;

final class BroadcastEvent implements EventInterface
{
    public function __construct(
        private string $name,
        private array $payload = [],
        private ?object $target = null
    ) {
    }

    public function payload(): array
    {
        return $this->payload;
    }

    public function target(): ?object
    {
        return $this->target;
    }

    public function identifier(): string
    {
        return $this->name;
    }
}
