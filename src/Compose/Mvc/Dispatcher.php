<?php
/**
 * Created by PhpStorm.
 * User: alaminahmed
 * Date: 2016-11-02
 * Time: 2:17 PM
 */

namespace Compose\Mvc;


use Compose\System\Container\ContainerAwareTrait;
use Compose\System\Http\CommandInterface;
use Interop\Container\ContainerInterface;
use Interop\Http\ServerMiddleware\DelegateInterface;
use Interop\Http\ServerMiddleware\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Class Dispatcher
 *
 * @package Compose\Mvc
 */
class Dispatcher implements MiddlewareInterface
{
    use CommandResolverTrait, ContainerAwareTrait;

    /**
     * Request attribute key hold the path param
     */
    const PARAM_PATH = 'paramPath';

    protected
        /**
         * @var array
         */
        $routes = [];

    /**
     * RequestHandler constructor.
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->setContainer($container);
    }

    /**
     * @todo validate if route is already registered
     * @param string $path
     * @param $command
     * @throws \Exception
     */
    public function route(string $path, $command)
    {
        if(isset($this->routes[$path])) {
            throw new \Exception("Path: {$path} is already registered.");
        }

        $path = trim($path, '/');
        $this->routes[$path] = $command;
    }


    /**
     * @param ServerRequestInterface $request
     * @param DelegateInterface $delegate
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, DelegateInterface $delegate) : ResponseInterface
    {
        $response = $this->dispatch($request);
        if(!$response) {
            $response = $delegate->process($request);
        }

        return $response;
    }


    /**
     * Dispatches to command pattern
     *
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     * @throws \Exception
     */
    public function dispatch(ServerRequestInterface $request) : ?ResponseInterface
    {
        $uri = $request->getUri();
        $path = $uri->getPath();

        $matched = $this->match($path);
        if(!$matched) {
            return null;
        }

        [$command, $param] = $matched; // extract the tuple
        $request = $request->withAttribute(self::PARAM_PATH, $param);

        /** @var CommandInterface $instance */
        $instance = $this->resolveCommand($command, $this->container);
        return $instance->execute($request);
    }


    /**
     * Match requested resource/service with registered ones
     *
     * @param string $path
     * @return null|array
     */
    protected function match(string $path)
    {
        $path = $this->normalizePath($path);
        $paths = $this->routes;
        krsort($paths); // reverse so longer url string comes first

        foreach($paths as $url => $command) {
            $url = $this->normalizePath($url);
            if(strpos($path, $url, 0) === 0) { // this check is super fast. faster than regex.
                return [$command, str_replace($url, '', $path)];
            }
        }

        return null;
    }

    /**
     * @param string $path
     * @return string
     */
    protected function normalizePath(string $path) : string
    {
        return trim($path, '/') . '/';
    }
}
