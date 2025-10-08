<?php

declare(strict_types=1);

namespace Compose\Support\Error;

use Compose\Container\ResolvableInterface;
use Compose\Http\Exception\HttpException;
use Compose\Mvc\ViewEngineInterface;
use Compose\Support\Configuration;
use Laminas\Diactoros\Response;
use Laminas\Diactoros\ServerRequest;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Throwable;

/**
 * Class DebugResponseGenerator
 * @package Ats\Support\Error
 */
class ErrorResponseGenerator implements ResolvableInterface
{
    protected ViewEngineInterface $engine;

    protected bool $debug;

    protected string $template_debug = 'compose::error/debug';

    protected string $template_404 = 'compose::error/404';

    protected string $template_error = 'compose::error/error';


    /**
     * ErrorResponseGenerator constructor.
     * @param ViewEngineInterface $engine
     * @param Configuration $configuration
     */
    public function __construct(ViewEngineInterface $engine, Configuration $configuration)
    {
        $this->engine = $engine;
        $this->debug = $configuration['debug'] ?? false;
    }

    /**
     * @param $exception
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @return ResponseInterface
     * @throws Exception
     */
    public function __invoke(Throwable $exception, ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        if($this->debug) {
            $template = $this->template_debug;
            $httpStatus = 500;
        } else if($exception instanceof HttpException && $exception->getCode() == 404) {
            $template = $this->template_404;
            $httpStatus = 404;
        } else {
            $template = $this->template_error;
            $httpStatus = 500;
        }

        $response = $response->withStatus($httpStatus);
        $response->getBody()->write($this->engine->render($template, compact('exception', 'request'), $request));

        return $response;
    }

    public function renderWithoutRequest(Throwable $exception, ?ServerRequestInterface $request = null, ?ResponseInterface $response = null): ResponseInterface
    {
        $request ??= new ServerRequest();
        $response ??= new Response();

        return $this($exception, $request, $response);
    }
}