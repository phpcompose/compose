<?php

namespace Compose\Mvc;

use Psr\Http\Message\ServerRequestInterface;

interface ViewEngineInterface
{
    public function render(string $template, array $data = [], ?ServerRequestInterface $request = null, ?string $layout = null): string;

    public function addPath(string $namespace, string $path): void;

    public function registerHelper(string $alias, callable $helper): void;

    public function hasTemplate(string $template): bool;

    public function resolvePath(string $template): ?string;

    /**
     * @return array<string, string>
     */
    public function getPaths(): array;
}
