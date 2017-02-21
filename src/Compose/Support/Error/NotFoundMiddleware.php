<?php
/**
 * Created by PhpStorm.
 * User: Alamin
 */

namespace Compose\Support\Error;


use Compose\Mvc\ViewRendererInterface;
use Compose\System\Container\ServiceAwareInterface;
use Compose\System\Http\Exception\HttpException;
use Compose\System\Http\MiddlewareCommand;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response;

class NotFoundMiddleware extends MiddlewareCommand implements ServiceAwareInterface
{
    protected
        /**
         * @var ErrorResponseGenerator
         */
        $generator;

    /**
     * NotFoundMiddleware constructor.
     *
     * @param ErrorResponseGenerator $generator
     */
    function __construct(ErrorResponseGenerator $generator)
    {
        $this->generator = $generator;
    }

    /**
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     * @throws HttpException
     */
    protected function onExecute(ServerRequestInterface $request): ResponseInterface
    {
        $exception = new HttpException(sprintf("%s Not found", $request->getUri()->__toString()), 404);
        $generator = $this->generator;

        return $generator($exception, $request, new Response());
    }
}