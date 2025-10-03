<?php

namespace Compose\Mvc\Helper;

use Compose\Mvc\HelperRegistryInterface;
use Compose\Mvc\View;
use Psr\Http\Message\ServerRequestInterface;

class LayoutHelper implements HelperInterface
{
    protected $content;
    protected $sections = [];
    protected $data = [];

    private ?HelperRegistryInterface $registry = null;

    public function __invoke(HelperRegistryInterface $helpers, ...$args)
    {
        $this->registry = $helpers;
        return $this;
    }

    public function setRegistry(HelperRegistryInterface $registry): void
    {
        $this->registry = $registry;
    }

    public function share(string $key, $data): void
    {
        $this->data[$key] = $data;
    }

    public function shared(string $key)
    {
        return $this->data[$key] ?? null;
    }

    public function content(string $content = null): ?string
    {
        if ($content !== null) {
            $this->content = $content;
            $this->sections['content'] = $content;
            return null;
        }

        return $this->content;
    }

    public function start(string $name): void
    {
        ob_start(function($buffer) use ($name) {
            $this->sections[$name] = $buffer;
            return $buffer;
        });
    }

    public function end(): void
    {
        ob_end_clean();
    }

    public function section(string $name): ?string
    {
        if (isset($this->sections[$name])) {
            return $this->sections[$name];
        }

        if ($name === 'content') {
            return $this->content;
        }

        return null;
    }

    public function view(): ?View
    {
        return $this->registry ? $this->registry->getCurrentView() : null;
    }

    public function request(): ?ServerRequestInterface
    {
        return $this->registry ? $this->registry->getCurrentRequest() : null;
    }
}
