<?php
namespace Compose\Support\Error;


use Compose\Container\ResolvableInterface;
use Compose\Container\ServiceAwareInterface;
use Compose\Container\ServiceInterface;
use Compose\Http\Exception\HttpException;
use Compose\Mvc\ViewRenderer;
use Compose\Mvc\ViewRendererInterface;
use Compose\Support\Configuration;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class DebugResponseGenerator
 * @package Ats\Support\Error
 */
class ErrorResponseGenerator implements ResolvableInterface
{
    protected
        /**
         * @var ViewRenderer
         */
        $renderer,

        /**
         * @var bool
         */
        $debug,

        /**
         * @var string template to debug view
         */
        $template_debug = 'compose::error/debug',

        /**
         * @var string template to 404 page, for production
         */
        $template_404 = 'compose::error/404',

        /**
         * @var null
         */
        $template_error = 'compose::error/error';


    /**
     * ErrorResponseGenerator constructor.
     * @param ViewRenderer $renderer
     * @param Configuration $configuration
     */
    public function __construct(ViewRendererInterface $renderer, Configuration $configuration)
    {
        $this->renderer = $renderer;
        $this->debug = $configuration['debug'] ?? false;
    }

    /**
     * @param $exception
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @return ResponseInterface
     * @throws \Exception
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
        $response->getBody()->write($this->renderer->render(
            $template,
            compact('exception', 'request'))
        );

        return $response;
    }
}