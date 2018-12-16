<?php
/**
 * Created by PhpStorm.
 * User: alaminahmed
 * Date: 2018-06-23
 * Time: 2:21 PM
 */

namespace Compose\Http;


use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ResponseInterface;

abstract class RequestHandler implements MiddlewareInterface, RequestHandlerInterface
{
    use ResponseHelperTrait;

    /**
     * Implementing command interface
     *
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     * @throws \Throwable
     */
    final public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        return $this->handle($request);
    }

    /**
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     * @throws \Throwable
     */
    final public function handle(ServerRequestInterface $request): ResponseInterface
    {
        try {
            $this->onInit($request);

            $response = $this->onHandle($request);

            $this->onExit($request, $response);
        }

        catch(\Exception $exception) {
            $response = $this->onException($request, $exception);
        }

        return $response;
    }

    /**
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    abstract protected function onHandle(ServerRequestInterface $request) : ResponseInterface;

    /**
     * Command initialize method
     */
    protected function onInit(ServerRequestInterface $request) {}

    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     */
    protected function onExit(ServerRequestInterface $request, ResponseInterface $response) {}

    /**
     * @param ServerRequestInterface $request
     * @param \Throwable $e
     * @return null|ResponseInterface
     * @throws \Throwable
     */
    protected function onException(ServerRequestInterface $request, \Throwable $e) : ?ResponseInterface
    {
        throw $e;
    }

}