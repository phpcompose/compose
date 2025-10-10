<?php

namespace Compose\Bridge\Plates;

use Compose\Template\RendererInterface;
use League\Plates\Engine;
use League\Plates\Template\Template;
use Psr\Http\Message\ServerRequestInterface;

class PlatesViewEngine implements RendererInterface
{
    private Engine $engine;
    private ?string $defaultLayout;

    public function __construct(Engine $engine, ?string $defaultLayout = null)
    {
        $this->engine = $engine;
        $this->defaultLayout = $defaultLayout;
    }

    public function render(string $template, array $data = [], ?ServerRequestInterface $request = null, ?string $layout = null): string
    {
        if ($request && !array_key_exists('request', $data)) {
            $data['request'] = $request;
        }

        $content = $this->engine->render($template, $data);

        $layoutName = $layout ?? $this->defaultLayout;
        if ($layoutName) {
            $layoutData = $data;
            $layoutData['content'] = $content;

            return $this->engine->render($layoutName, $layoutData);
        }

        return $content;
    }

    public function addPath(string $namespace, string $path): void
    {
        $this->engine->addFolder($namespace, $path, true);
    }

    public function registerHelper(string $alias, callable $helper): void
    {
        $this->engine->registerFunction($alias, $helper);
    }

    public function hasTemplate(string $template): bool
    {
        return $this->engine->exists($template);
    }

    public function resolvePath(string $template): ?string
    {
        $path = $this->engine->path($template);
        return $path ?: null;
    }

    public function getPaths(): array
    {
        $paths = [];
        foreach ($this->engine->getFolders() as $folder) {
            $paths[$folder->getName()] = $folder->getPath();
        }

        return $paths;
    }

    public function getEngine(): Engine
    {
        return $this->engine;
    }
}
