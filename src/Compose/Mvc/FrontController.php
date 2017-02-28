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
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Zend\Diactoros\Response;
use Zend\Stratigility\MiddlewarePipe;

/**
 * Class FrontController for Compose apps
 *
 * @package Compose\Mvc
 */
class FrontController extends MiddlewarePipe
{
    use CommandResolverTrait, ContainerAwareTrait;

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
        parent::__construct();
        $this->setResponsePrototype(new Response());
        $this->setContainer($container);
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

        $command = $this->routes[$path];
        if(!$command) {
            throw new \Exception("Unable to find command for path: $path");
        }

        /** @var CommandInterface $instance */
        $instance = $this->resolveCommand($command, $this->container);
        return $instance->execute($request);
    }
}
