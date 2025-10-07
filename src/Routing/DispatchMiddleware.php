<?php
namespace Compose\Routing;

use Compose\Container\ContainerAwareInterface;
use Compose\Container\ContainerAwareTrait;
use Compose\Http\Event\DispatchEvent;
use Compose\Http\Event\ExceptionEvent;
use Compose\Http\Event\ResponseEvent;
use Psr\EventDispatcher\EventDispatcherInterface;
use Exception;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class DispatchMiddleware implements MiddlewareInterface, ContainerAwareInterface
{
    use ContainerAwareTrait;

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler) : ResponseInterface
    {
        $dispatcher = $this->getContainer()->get(EventDispatcherInterface::class);

        try {
            $route = $request->getAttribute(Route::class);

            if($route) {
                $dispatcher->dispatch(new DispatchEvent($route, $request));
                $handler = $route->handler;

                $instance = $this->resolveHandler($handler);
                $response = $instance->handle($request);

                $dispatcher->dispatch(new ResponseEvent($response));
                return $response;
            }

        } catch (Exception $e) {
            $dispatcher->dispatch(new ExceptionEvent($e));
            throw $e; // for now just passing to the error handler
        }

        return $handler->handle($request);
    }

    protected function resolveHandler($mixed) : RequestHandlerInterface
    {
        $container = $this->getContainer();
        $handler = null;
        if(is_string($mixed)) {
            $handler = $container->get($mixed);
        } else {
            $handler = $mixed;
        }

        if(!$handler) {
            throw new Exception(sprintf("Unable to resolve Command %s",
                is_object($mixed) ? get_class($mixed) : $mixed));
        }

        if(!$handler instanceof RequestHandlerInterface) {
            throw new Exception("Command must be instance of RequestHandlerInterface.");
        }

        return $handler;
    }
}
