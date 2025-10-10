<?php

namespace Compose\Handler;

use ArrayObject;
use Compose\Container\ContainerAwareInterface;
use Compose\Container\ContainerAwareTrait;
use Compose\Http\Exception\HttpException;
use Compose\Routing\Route;
use Compose\Support\Invocation;
use Compose\Template\RendererInterface;
use Exception;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use ReflectionException;

/**
 * Multi-action request handler akin to traditional MVC controllers.
 */
abstract class ActionHandler extends RequestHandler implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    /**
     * Map of HTTP methods to action names.
     *
     * @var array<string,string>
     */
    protected array $httpMethodMapper = [
        'index' => 'index',
        'get' => 'get',
        'post' => 'post',
        'put' => 'put',
        'patch' => 'patch',
        'delete' => 'delete',
    ];

    protected string $actionNamePrefix = 'do';
    protected string $actionNameSuffix = '';
    protected string $defaultAction = 'index';

    /**
     * @throws HttpException
     * @throws ReflectionException
     */
    protected function onHandle(ServerRequestInterface $request): ResponseInterface
    {
        $invocation = $this->resolveActionHandler($request);
        return $invocation();
    }

    /**
     * Resolve which action method should execute the request.
     *
     * @throws HttpException
     */
    protected function resolveActionHandler(ServerRequestInterface $request): Invocation
    {
        /** @var Route|ArrayObject|null $route */
        $route = $request->getAttribute(Route::class);
        if (!$route) {
            $path = $request->getUri()->getPath();
            $route = new ArrayObject([
                'method' => $request->getMethod(),
                'path' => $path,
                'params' => array_values(array_filter(explode('/', $path))),
            ], ArrayObject::ARRAY_AS_PROPS);
        }

        $action = $this->resolveActionName($route);
        $params = $route->params;

        $invocation = Invocation::fromCallable([$this, $action]);
        if (!$invocation) {
            throw new HttpException(
                "Unable to find action for request: {$route->method}: {$route->path} in " . static::class,
                404
            );
        }

        if ($invocation->getArgumentTypeAtIndex(0) === ServerRequestInterface::class) {
            array_unshift($params, $request);
        }

        $invocation->setParameters($params);
        return $invocation;
    }

    protected function resolveActionName(ArrayObject $route): string
    {
        $httpMethod = strtolower($route->method);
        if (isset($this->httpMethodMapper[$httpMethod])) {
            $httpMethod = $this->httpMethodMapper[$httpMethod];
        }

        if (!count($route->params) && $httpMethod === 'get') {
            return $this->buildActionName($this->defaultAction);
        }

        $restMethod = $this->buildActionName($httpMethod);
        if (method_exists($this, $restMethod)) {
            return $restMethod;
        }

        $action = $this->filterActionName(array_shift($route->params));
        return $restMethod . ucfirst((string) $action);
    }

    protected function buildActionName(string ...$names): string
    {
        array_unshift($names, $this->actionNamePrefix);
        $names[] = $this->actionNameSuffix;

        return lcfirst(implode('', array_map('ucfirst', $names)));
    }

    protected function filterActionName(?string $action = null): ?string
    {
        if (!$action) {
            return null;
        }

        $allowedChars = ['-'];
        if ($allowedChars) {
            $action = str_replace(' ', '', str_replace($allowedChars, ' ', $action));
        }

        $regex = "/^[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*$/";
        if (!preg_match($regex, $action)) {
            return null;
        }

        return $action;
    }

    /**
     * Template rendering helper.
     *
     * @throws Exception
     */
    protected function render(string $template, ?array $data = null, int $status = 200, array $headers = []): ResponseInterface
    {
        /** @var RendererInterface $engine */
        $engine = $this->getContainer()->get(RendererInterface::class);
        if (!$engine) {
            throw new Exception('Template renderer not found in the container.');
        }

        $html = $engine->render($template, $data ?? [], $this->request);

        return $this->html($html, $status, $headers);
    }
}
