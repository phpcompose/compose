<?php
/**
 * Created by PhpStorm.
 * User: alaminahmed
 * Date: 2017-10-27
 * Time: 11:59 AM
 */

namespace Compose\Http;


use Compose\Container\ContainerAwareInterface;
use Compose\Container\ContainerAwareTrait;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Zend\Stratigility\Middleware\CallableMiddlewareDecorator;
use Zend\Stratigility\MiddlewarePipe;
use Zend\Diactoros\Server;

/**
 * Lazy Middleware factory
 * @param $mixed
 * @param ContainerInterface $container
 * @return MiddlewareInterface
 * @throws \Exception
 */
function middleware($mixed, ContainerInterface $container) : MiddlewareInterface
{
    if(is_string($mixed)) {
        return new ResolvableMiddleware($mixed, $container);
    } else if(is_callable($mixed)) {
        return new CallableMiddlewareDecorator($mixed);
    } else if(is_object($mixed) && $mixed instanceof MiddlewareInterface) {
        return $mixed;
    } else if(is_array($mixed)) {
        $pipe = new MiddlewarePipe();
        foreach($pipe as $middleware) {
            $pipe->pipe(middleware($middleware, $container));
        }
        return $pipe;
    } else {
        throw new \Exception("Unable to decorate Middleware");
    }
}


/**
 * Class Pipeline
 * @package Compose\Http
 */
class Pipeline  implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    protected
        /**
         * @var Server
         */
        $server,

        /**
         * @var MiddlewarePipe
         */
        $pipe;

    /**
     * Pipeline constructor.
     */
    public function __construct()
    {
        $this->pipe = new MiddlewarePipe();
        $this->server = Server::createServer(
            $this,
            $_SERVER, $_GET, $_POST, $_COOKIE, $_FILES
        );
    }

    /**
     * @param ServerRequestInterface $request
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function __invoke(ServerRequestInterface $request)
    {
       return $this->pipe->handle($request);
    }

    /**
     * @param $middleware
     * @return Pipeline
     * @throws \Exception
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function pipe($middleware) : self
    {
        $instance = middleware($middleware, $this->getContainer());
        $this->pipe->pipe($instance);

        return $this;
    }

    /**
     * @param array|null $arr
     * @return Pipeline
     * @throws \Exception
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function pipeMany(array $arr = null): self
    {
        if($arr) {
            foreach($arr as $key => $middleware) {
                $this->pipe($middleware);
            }
        }

        return $this;
    }

    /**
     */
    public function listen()
    {
        $this->server->listen();
    }
}