<?php

namespace Compose\Http\Session;

use SessionHandlerInterface;

interface SessionStorageInterface
{
    public function start(?SessionHandlerInterface $handler = null, array $options = []): void;

    public function isStarted(): bool;

    public function getId(): string;

    public function setId(string $id): void;

    public function has(string $name): bool;

    public function get(string $name, $default = null);

    public function set(string $name, $value): void;

    public function remove(string $name): void;

    public function regenerate(bool $deleteOldSession = true): void;

    public function all(): array;

    public function clear(): void;

    public function close(): void;
}
