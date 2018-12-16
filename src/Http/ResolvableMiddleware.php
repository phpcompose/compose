<?php
/**
 * Created by PhpStorm.
 * User: alaminahmed
 * Date: 2018-06-24
 * Time: 10:31 AM
 */

namespace Compose\Http;


use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Class ResolvableMiddleware
 * @package Compose\Http
 */
class ResolvableMiddleware implements MiddlewareInterface
{
    protected
        $className,
        /** @var ContainerInterface */
        $container;

    /**
     * ResolvableMiddleware constructor.
     * @param string $className
     * @param ContainerInterface $container
     */
    public function __construct(string $className, ContainerInterface $container)
    {
        $this->className = $className;
        $this->container = $container;
    }

    /**
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     * @throws \Exception
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $instance = $this->container->get($this->className);
        if($instance instanceof MiddlewareInterface) {
            return $instance->process($request, $handler);
        } else if($instance instanceof RequestHandlerInterface) {
            return $instance->handle($request);
        } else {
            throw new \Exception("{$this->className} is NOT Middleware or RequestHandler");
        }
    }
}