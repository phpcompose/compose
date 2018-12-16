<?php
namespace Compose\Mvc;


use Compose\Container\ContainerAwareInterface;
use Compose\Container\ContainerAwareTrait;
use Compose\Event\EventNotifierInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Class FrontController
 *
 * Front Controller for Compose MVC application
 * @package Compose\Mvc
 */
class RoutingMiddleware implements MiddlewareInterface, ContainerAwareInterface
{
    use ContainerAwareTrait;

    const
        EVENT_ROUTE = 'http.route',
        EVENT_ERROR = 'http.error';

    protected
        /**
         * @var array
         */
        $routes = [];


    /**
     * Add a routing map
     *
     * @param RouteInfo $route
     * @throws \Exception
     */
    public function route(RouteInfo $route) : void
    {
        $path = $route->path;
        if(isset($this->routes[$path])) {
            throw new \Exception("Route already mapped for path: {$path}");
        }


        $path = trim($path, '/');
        $this->routes[$path] = $route;
    }

    /**
     * Attempt to route the request to appropriate route map
     * @param ServerRequestInterface $request
     * @return RouteInfo|null
     */
    public function match(ServerRequestInterface $request) : ?RouteInfo
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

            /** @var RouteInfo $route */
            $route = $routes[$routePath];

            if(strpos($requestedPath, $normalizedRoutePath, 0) === 0) {
                $paramString = str_replace($normalizedRoutePath, '', $requestedPath);
                if(!empty($paramString)) {
                    $params = array_values(array_filter(explode('/', $paramString)));
                } else {
                    $params = [];
                }

                return RouteInfo::fromArray([
                    'method' =>  $request->getMethod(),
                    'path' => $routePath,
                    'params' => $params,
                    'handler' => $route->handler
                ]);
            }
        }

        return null;
    }

    /**
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     * @throws \Exception
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler) : ResponseInterface
    {
        /** @var EventNotifierInterface $notifier */
        $notifier = $this->getContainer()->get(EventNotifierInterface::class);

        $notifier->notify(self::EVENT_ROUTE, ['request' => $request], $this);
        $route = $this->match($request);

        if($route) {
            $request = $request->withAttribute(RouteInfo::class, $route);
        }

        return $handler->handle($request);
    }
}
