<?php

namespace Compose\Template;

use Compose\Template\Helper\HelperRegistry;
use Compose\Template\Helper\HelperRegistryInterface;
use Exception;
use Psr\Http\Message\ServerRequestInterface;

class TemplateRenderer implements RendererInterface
{
    private HelperRegistry $helpers;
    private array $folders = [];
    private array $maps = [];
    private ?string $baseDirectory;
    private string $extension;

    public function __construct(array $templates, HelperRegistry $helpers)
    {
        $this->helpers = $helpers;
        $this->folders = $templates['folders'] ?? [];
        $this->maps = $templates['maps'] ?? [];
        $this->baseDirectory = $templates['dir'] ?? null;
        $this->extension = $templates['extension'] ?? 'phtml';
    }

    public function render(string $template, array $data = [], ?ServerRequestInterface $request = null, ?string $layout = null): string
    {
        $view = new Template($template, $data);
        $view->layout = $layout;
        $this->helpers->setContext($view, $request);
        $view->setHelperRegistry($this->helpers);

        try {
            $content = $this->renderScript($view->getScript(), $view->getArrayCopy(), $view);

            if ($view->layout) {
                $view->set(Template::CONTENT, $content);
                $layoutData = $view->toArray();
                $content = $this->renderScript($view->layout, $layoutData, $view);
            }

            return $content;
        } finally {
            $this->helpers->setContext(null, null);
        }
    }

    public function addPath(string $namespace, string $path): void
    {
        $this->folders[$namespace] = $path;
    }

    public function registerHelper(string $alias, callable $helper): void
    {
        $this->helpers->register($alias, function(HelperRegistryInterface $helpers, ...$arguments) use ($helper) {
            return $helper(...$arguments);
        });
    }

    public function hasTemplate(string $template): bool
    {
        return $this->resolvePath($template) !== null;
    }

    public function resolvePath(string $template): ?string
    {
        return $this->resolve($template);
    }

    public function getPaths(): array
    {
        $paths = [];
        if ($this->baseDirectory) {
            $paths['__base__'] = $this->baseDirectory;
        }

        foreach ($this->folders as $name => $dir) {
            $paths[$name] = $dir;
        }

        return $paths;
    }

    private function renderScript(string $script, ?array $locals = null, ?object $bind = null): string
    {
        $filename = $this->resolve($script);
        if (!$filename) {
            throw new Exception("Unable to resolve view script: " . $script);
        }

        $closure = \Closure::bind(function(string $__filename, array $__data) {
            ob_start();
            extract($__data);

            include $__filename;
            return ob_get_clean();
        }, $bind);

        return $closure($filename, $locals ?? []);
    }

    private function resolve(string $scriptName): ?string
    {
        $scriptName = $this->maps[$scriptName] ?? $scriptName;

        $file = realpath($scriptName);
        if ($file) {
            return $file;
        }

        $parts = explode('::', $scriptName);
        if (count($parts) === 2) {
            [$namespace, $name] = $parts;
            $dir = $this->folders[$namespace] ?? null;
            $script = $dir ? rtrim($dir, '/') . '/' . $name : $name;
        } else {
            $script = $parts[0];
            if ($this->baseDirectory) {
                $script = rtrim($this->baseDirectory, '/') . '/' . $script;
            }
        }

        $fileInfo = pathinfo($script);
        if (!isset($fileInfo['extension'])) {
            $script = $script . '.' . $this->extension;
        }

        return file_exists($script) ? $script : null;
    }
}
