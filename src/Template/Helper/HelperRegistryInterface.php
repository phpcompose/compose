<?php

namespace Compose\Template\Helper;

use Compose\Template\Template;
use Psr\Http\Message\ServerRequestInterface;

interface HelperRegistryInterface
{
    public function register(string $name, $definition): void;

    public function has(string $name): bool;

    public function get(string $name);

    public function call(string $name, ...$arguments);

    public function setContext(?Template $view, ?ServerRequestInterface $request): void;

    public function getCurrentView(): ?Template;

    public function getCurrentRequest(): ?ServerRequestInterface;
}
