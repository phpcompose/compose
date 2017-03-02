<?php
/**
 * Created by PhpStorm.
 * User: Alamin
 */

namespace Compose\Support\Error;


use Compose\System\Container\ServiceAwareInterface;
use Compose\System\Http\Exception\HttpException;
use Interop\Http\ServerMiddleware\DelegateInterface;
use Interop\Http\ServerMiddleware\MiddlewareInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response;

class NotFoundMiddleware implements MiddlewareInterface, ServiceAwareInterface
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
    public function __construct(ErrorResponseGenerator $generator)
    {
        $this->generator = $generator;
    }

    /**
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     * @throws HttpException
     */
    public function process(ServerRequestInterface $request, DelegateInterface $delegate): ResponseInterface
    {
        $exception = new HttpException(sprintf("%s Not found", $request->getUri()->__toString()), 404);
        $generator = $this->generator;

        return $generator($exception, $request, new Response());
    }
}