<?php
/**
 * Created by PhpStorm.
 * User: alaminahmed
 * Date: 2016-11-02
 * Time: 2:17 PM
 */

namespace Compose\Mvc;


use Compose\System\Container\ServiceFactoryInterface;
use Compose\System\Http\CommandInterface;
use Interop\Container\ContainerInterface;
use Interop\Http\Middleware\DelegateInterface;
use Zend\Stratigility\MiddlewarePipe;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Class FrontController for Compose apps
 *
 * @package Compose\Mvc
 */
class FrontController extends MiddlewarePipe
{
    use CommandResolverTrait;

    protected
        /**
         * @var ContainerInterface
         */
        $container,

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
        parent::__construct();
        $this->container = $container;
    }


    /**
     * Get the dependency container
     * @return ContainerInterface
     */
    public function getContainer() : ContainerInterface
    {
        return $this->container;
    }

    /**
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

        $this->pipe($path, [$this, 'dispatch']);
    }

    /**
     * Implement middleware interface
     *
     * @param ServerRequestInterface $request
     * @param DelegateInterface|null $delegate
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, DelegateInterface $delegate)
    {
        return parent::process($request, $delegate);
    }

    /**
     * Dispatches to command pattern
     *
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     * @throws \Exception
     */
    public function dispatch(ServerRequestInterface $request) : ResponseInterface
    {
        $uri = $request->getUri();
        $originalUri = $request->getAttribute('originalUri');
        $path = trim(str_replace($uri->getPath(), '', $originalUri->getPath()), '/');

        if(!isset($this->routes[$path])) {
            throw new \Exception("Unable to dispatch: {$originalUri->getPath()}");
        }

        $command = $this->routes[$path];

        /** @var CommandInterface $instance */
        $instance = $this->resolveCommand($command, $this->container);

        return $instance->execute($request);
    }
}
