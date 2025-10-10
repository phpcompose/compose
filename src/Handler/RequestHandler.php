<?php

namespace Compose\Handler;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Throwable;

abstract class RequestHandler implements MiddlewareInterface, RequestHandlerInterface
{
    use ResponseHelperTrait;

    protected ServerRequestInterface $request;

    final public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        return $this->handle($request);
    }

    final public function handle(ServerRequestInterface $request): ResponseInterface
    {
        try {
            $this->request = $request;
            $this->onInit($request);

            $response = $this->onHandle($request);

            $this->onExit($request, $response);
        } catch (Throwable $exception) {
            $response = $this->onException($request, $exception);
        }

        return $response;
    }

    abstract protected function onHandle(ServerRequestInterface $request): ResponseInterface;

    protected function onInit(ServerRequestInterface $request): void
    {
    }

    protected function onExit(ServerRequestInterface $request, ResponseInterface $response): void
    {
    }

    /**
     * @throws Throwable
     */
    protected function onException(ServerRequestInterface $request, Throwable $e): ?ResponseInterface
    {
        $e->request = $request;
        throw $e;
    }
}
