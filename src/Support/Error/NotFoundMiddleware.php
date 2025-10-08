<?php
namespace Compose\Support\Error;


use Compose\Container\ResolvableInterface;
use Compose\Http\Exception\HttpException;
use Laminas\Diactoros\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Throwable;

class NotFoundMiddleware implements MiddlewareInterface, ResolvableInterface
{
    protected ErrorResponseGenerator $generator;

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
     * @param RequestHandlerInterface $delegate
     * @return ResponseInterface
     * @throws Exception
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $delegate): ResponseInterface
    {
        $exception = new HttpException(sprintf('%s Not found', $request->getUri()->__toString()), 404);

        return ($this->generator)($exception, $request, new Response());
    }
}