<?php
/**
 * Created by PhpStorm.
 * User: alaminahmed
 * Date: 2018-06-24
 * Time: 10:06 AM
 */

namespace Compose\Mvc;


use Compose\Container\ContainerAwareInterface;
use Compose\Container\ContainerAwareTrait;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Zend\Stratigility\MiddlewarePipe;

use function Zend\Stratigility\path;
use function Compose\Http\middleware;

class MvcMiddleware implements MiddlewareInterface, ContainerAwareInterface
{
    use ContainerAwareTrait;

    protected
        /**
         * @var MiddlewarePipe
         */
        $pipeline;

    /**
     * MvcMiddleware constructor.
     */
    public function __construct()
    {
        $this->pipeline = new MiddlewarePipe();
    }

    /**
     * @param string $path
     * @param $handler
     * @throws \Exception
     */
    public function route(string $path, $handler)
    {
        $this->pipeline->pipe(path($path, middleware($handler, $this->getContainer())));
    }

    /**
     * @param MiddlewareInterface $middleware
     */
    public function pipe(MiddlewareInterface $middleware)
    {
        $this->pipeline->pipe($middleware);
    }

    /**
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        return $this->pipeline->process($request, $handler);
    }
}