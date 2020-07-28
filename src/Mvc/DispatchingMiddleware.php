<?php
namespace Compose\Mvc;


use Compose\Container\ContainerAwareInterface;
use Compose\Container\ContainerAwareTrait;
use Compose\Event\EventDispatcherInterface;
use Compose\Event\ExceptionMessage;
use Compose\Event\Message;
use Exception;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
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
class DispatchingMiddleware implements MiddlewareInterface, ContainerAwareInterface
{
    use ContainerAwareTrait;

    const
        EVENT_DISPATCH = 'http.dispatch',
        EVENT_RESPONSE = 'http.response',
        EVENT_ERROR = 'http.error';


    /**
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     * @throws Exception
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler) : ResponseInterface
    {
        /** @var EventDispatcherInterface $notifier */
        $notifier = $this->getContainer()->get(EventDispatcherInterface::class);

        try {
            $route = $request->getAttribute(RouteInfo::class);

            if($route) {
                $notifier->dispatch(new Message(self::EVENT_DISPATCH, ['routeMap' => $route, 'request' => $request], $this));
                $handler = $route->handler;

                $instance = $this->resolveHandler($handler);
                $response = $instance->handle($request);

                $notifier->dispatch(new Message(self::EVENT_RESPONSE, ['response' => $response], $this));
                return $response;
            }

        } catch (Exception $e) {
            $notifier->dispatch(new ExceptionMessage($e));
            throw $e; // for now just passing to the error handler
        }


        return $handler->handle($request);
    }


    /**
     * @param $mixed
     * @return RequestHandlerInterface
     * @throws Exception
     */
    protected function resolveHandler($mixed) : RequestHandlerInterface
    {
        $container = $this->getContainer();
        $handler = null;
        if(is_string($mixed)) { // for string, we will assume class name and will try to resolve by container
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
