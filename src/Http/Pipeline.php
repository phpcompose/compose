<?php
/**
 * Created by PhpStorm.
 * User: alaminahmed
 * Date: 2017-10-27
 * Time: 11:59 AM
 */

namespace Compose\Http;

use Exception;
use Compose\Container\ContainerAwareInterface;
use Compose\Container\ContainerAwareTrait;
use Compose\Support\Error\ErrorResponseGenerator;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Laminas\Stratigility\Middleware\CallableMiddlewareDecorator;
use Laminas\Stratigility\MiddlewarePipe;
use Laminas\HttpHandlerRunner\RequestHandlerRunner;
use Laminas\HttpHandlerRunner\Emitter\SapiEmitter;
use Laminas\Diactoros\ServerRequestFactory;

/**
 * Lazy Middleware factory
 * @param $mixed
 * @param ContainerInterface $container
 * @return MiddlewareInterface
 * @throws Exception
 */
function middleware($mixed, ContainerInterface $container) : MiddlewareInterface
{
    if(is_string($mixed)) {
        return new ResolvableMiddleware($mixed, $container);
    } else if(is_callable($mixed)) {
        return new CallableMiddlewareDecorator($mixed);
    } else if($mixed instanceof MiddlewareInterface) {
        return $mixed;
    } else if(is_array($mixed)) {
        $pipe = new MiddlewarePipe();
        foreach($pipe as $middleware) {
            $pipe->pipe(middleware($middleware, $container));
        }
        return $pipe;
    } else {
        throw new Exception("Unable to decorate Middleware");
    }
}


/**
 * Class Pipeline
 * @package Compose\Http
 */
class Pipeline  implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    protected MiddlewarePipe $pipe;

    /**
     * Pipeline constructor.
     */
    public function __construct()
    {
        $this->pipe = new MiddlewarePipe();
    }

    /**
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function __invoke(ServerRequestInterface $request): ResponseInterface
    {
       return $this->pipe->handle($request);
    }

    /**
     * @param $middleware
     * @return void
     * @throws Exception
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function pipe($middleware) : void
    {
        $instance = middleware($middleware, $this->getContainer());
        $this->pipe->pipe($instance);
    }

    /**
     * @param array|null $arr
     * @return void
     * @throws Exception
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function pipeMany(array $arr = null): void
    {
        if($arr) {
            foreach($arr as $middleware) {
                $this->pipe($middleware);
            }
        }
    }

    /**
     * Starts listening for incoming request through the pipeline
     */
    public function listen()
    {
        $container = $this->getContainer();
        $runner = new RequestHandlerRunner(
            $this->pipe,
            new SapiEmitter(),
            [ServerRequestFactory::class, 'fromGlobals'],
            $container->get(ErrorResponseGenerator::class)
        );
        $runner->run();
    }
}