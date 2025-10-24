<?php

namespace Compose\Pages;

use ArgumentCountError;
use Compose\Container\ContainerAwareInterface;
use Compose\Container\ContainerAwareTrait;
use Compose\Event\BroadcastEvent;
use Compose\Support\Invocation;
use Compose\Template\TemplateRenderer;
use Laminas\Diactoros\Response\HtmlResponse;
use InvalidArgumentException;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use TypeError;

class PagesMiddleware implements MiddlewareInterface, ContainerAwareInterface
{
    use ContainerAwareTrait;

    private TemplateRenderer $viewEngine;
    private ?string $baseAlias = null;
    private array $folders = [];
    private string $defaultPage = 'index';

    public function __construct(TemplateRenderer $engine)
    {
        $this->viewEngine = $engine;
    }

    public function setDirectory(string $dir, ?string $namespace = null): void
    {
        $this->baseAlias = $namespace ?: 'pages';
        $this->viewEngine->addPath($this->baseAlias, $dir);
    }

    public function addFolder(string $name, string $dir): void
    {
        $this->folders[$name] = $dir;
        $this->viewEngine->addPath($name, $dir);
    }

    public function setFolders(array $folders): void
    {
        $this->folders = $folders;
        foreach ($folders as $name => $dir) {
            $this->viewEngine->addPath($name, $dir);
        }
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $match = $this->matchTemplate($request->getUri()->getPath());
        if (!$match) {
            return $handler->handle($request);
        }
        [$template, $params] = $match;

        /** @var \Compose\Event\EventDispatcher $dispatcher */
        $dispatcher = $this->getContainer()->get(EventDispatcherInterface::class);

        $data = $this->executeCodeBehind($template, $params, $request);
        $dispatcher->dispatch(new BroadcastEvent('pages.match'));

        if ($data instanceof ServerRequestInterface) {
            return $handler->handle($data);
        }

        if ($data instanceof ResponseInterface) {
            return $data;
        }

        if ($data instanceof \Traversable) {
            $data = iterator_to_array($data);
        }

        if ($data !== null && !is_array($data)) {
            throw new \RuntimeException('Pages middleware expects data array; received ' . get_debug_type($data));
        }

        return new HtmlResponse($this->viewEngine->render($template, $data ?? [], $request));
    }

    private function matchTemplate(string $path): ?array
    {
        $path = trim($path, '/');
        $segments = $path === '' ? [] : explode('/', $path);
        $params = [];

        while (true) {
            foreach ($this->candidateTemplateNames($segments) as $name) {
                if ($this->viewEngine->hasTemplate($name)) {
                    return [$name, $params];
                }
            }

            if (empty($segments)) {
                break;
            }

            array_unshift($params, array_pop($segments));
        }

        return null;
    }

    private function executeCodeBehind(string $template, array $params, ServerRequestInterface $request): mixed
    {
        $path = $this->viewEngine->resolvePath($template);
        if (!$path) {
            return null;
        }

        $script = $path . '.php';
        if (!file_exists($script)) {
            return null;
        }

        $result = include $script;

        if ($result === false) {
            throw new \RuntimeException('Unable to include page script: ' . $script);
        }

        if ($result === 1 || $result === null) {
            return null;
        }

        if (is_callable($result)) {
            if (is_object($result) && $result instanceof ContainerAwareInterface) {
                $result->setContainer($this->getContainer());
            }

            $invocation = new Invocation($result);
            array_unshift($params, $request);

            try {
                return $invocation(...$params);
            } catch (ArgumentCountError|InvalidArgumentException|TypeError) {
                return $request;
            }
        }

        if (
            $result instanceof ResponseInterface ||
            $result instanceof ServerRequestInterface ||
            is_array($result) ||
            $result instanceof \Traversable
        ) {
            return $result;
        }

        throw new \RuntimeException('Invalid page script return type for ' . $script);
    }

    /**
     * @param array<int,string> $segments
     * @return string[]
     */
    private function candidateTemplateNames(array $segments): array
    {
        $names = [];

        $base = implode('/', $segments);

        if ($base !== '') {
            $names[] = $base;
            $names[] = $base . '/' . $this->defaultPage;
            if ($this->baseAlias) {
                $names[] = $this->baseAlias . '::' . $base;
                $names[] = $this->baseAlias . '::' . $base . '/' . $this->defaultPage;
            }
        } else {
            $names[] = $this->defaultPage;
            if ($this->baseAlias) {
                $names[] = $this->baseAlias . '::' . $this->defaultPage;
            }
        }

        if ($segments) {
            $first = $segments[0];
            if (isset($this->folders[$first])) {
                $remainder = array_slice($segments, 1);
                $portion = implode('/', $remainder);
                $names[] = $first . '::' . ($portion ?: $this->defaultPage);
                if ($portion) {
                    $names[] = $first . '::' . $portion . '/' . $this->defaultPage;
                }
            }
        }

        return array_values(array_unique($names));
    }
}
