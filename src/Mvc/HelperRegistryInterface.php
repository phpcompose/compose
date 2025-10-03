<?php

namespace Compose\Mvc;

use Psr\Http\Message\ServerRequestInterface;

interface HelperRegistryInterface
{
    public function register(string $name, $definition): void;

    public function has(string $name): bool;

    public function get(string $name);

    public function call(string $name, ...$arguments);

    public function setContext(?View $view, ?ServerRequestInterface $request): void;

    public function getCurrentView(): ?View;

    public function getCurrentRequest(): ?ServerRequestInterface;
}
