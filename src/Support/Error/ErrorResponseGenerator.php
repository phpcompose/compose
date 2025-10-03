<?php
namespace Compose\Support\Error;


use Compose\Container\ResolvableInterface;
use Compose\Http\Exception\HttpException;
use Compose\Mvc\ViewEngineInterface;
use Compose\Support\Configuration;
use Exception;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

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
    public function __invoke($exception, ServerRequestInterface $request, ResponseInterface $response) : ResponseInterface
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
}