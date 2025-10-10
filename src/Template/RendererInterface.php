<?php

namespace Compose\Template;

use Psr\Http\Message\ServerRequestInterface;

interface RendererInterface
{
    public function render(string $template, array $data = [], ?ServerRequestInterface $request = null, ?string $layout = null): string;
}
