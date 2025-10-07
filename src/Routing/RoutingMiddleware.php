<?php
namespace Compose\Routing;


use Compose\Container\ContainerAwareInterface;
use Compose\Container\ContainerAwareTrait;
use Compose\Http\Event\RouteEvent;
use Psr\EventDispatcher\EventDispatcherInterface;
use Exception;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class RoutingMiddleware implements MiddlewareInterface, ContainerAwareInterface
{
    use ContainerAwareTrait;

    protected
        /**
         * @var array
         */
        $routes = [];

    public function route(Route $route) : void
    {
        $path = trim($route->path, '/');

        if ($path === '') {
            throw new Exception('Route path cannot be empty.');
        }

        if(isset($this->routes[$path])) {
            throw new Exception("Route already mapped for path: {$path}");
        }

        $this->routes[$path] = $route;
    }

    public function match(ServerRequestInterface $request) : ?Route
    {
        $uri = $request->getUri();
        $normalize = function($path) {
            return trim($path, '/') . '/';
        };

        $routes = $this->routes;
        $routePaths = array_keys($routes);
        rsort($routePaths);

        $requestedPath = $normalize($uri->getPath());
        foreach($routePaths as $routePath) {
            $normalizedRoutePath = $normalize($routePath);

            $route = $routes[$routePath];

            if(strpos($requestedPath, $normalizedRoutePath, 0) === 0) {
                $paramString = str_replace($normalizedRoutePath, '', $requestedPath);
                if(!empty($paramString)) {
                    $params = array_values(array_filter(explode('/', $paramString)));
                } else {
                    $params = [];
                }

                return Route::fromArray([
                    'method' =>  $request->getMethod(),
                    'path' => $routePath,
                    'params' => $params,
                    'handler' => $route->handler
                ]);
            }
        }

        return null;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler) : ResponseInterface
    {
        $dispatcher = $this->getContainer()->get(EventDispatcherInterface::class);
        $dispatcher->dispatch(new RouteEvent($request));
        $route = $this->match($request);

        if($route) {
            $request = $request->withAttribute(Route::class, $route);
        }

        return $handler->handle($request);
    }
}
