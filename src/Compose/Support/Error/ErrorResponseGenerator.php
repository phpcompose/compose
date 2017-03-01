<?php
/**
 * Created by PhpStorm.
 * User: Alamin
 */

namespace Compose\Support\Error;


use Compose\Mvc\ViewRendererInterface;
use Compose\System\Container\ServiceAwareInterface;
use Compose\System\Http\Exception\HttpException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class DebugResponseGenerator
 * @package Ats\Support\Error
 */
class ErrorResponseGenerator implements ServiceAwareInterface
{
    const
        TEMPLATE_ERROR = 'compose::error/error',
        TEMPLATE_404 = 'compose::error/404',
        TEMPLATE_DEBUG = 'compose::error/debug';


    protected
        /**
         * @var ViewRendererInterface
         */
        $renderer,

        /**
         * @var bool
         */
        $debug,

        /**
         * @var string template to debug view
         */
        $template_debug = null,

        /**
         * @var string template to 404 page, for production
         */
        $template_404 = null,

        /**
         * @var null
         */
        $template_error = null;


    /**
     * ErrorResponseGenerator constructor.
     * @param ViewRendererInterface $renderer
     * @param array $templates
     * @param bool $debug
     * @throws \Exception
     */
    public function __construct(ViewRendererInterface $renderer, array $templates = [], bool $debug = true)
    {
        $this->renderer = $renderer;
        $this->debug = $debug;
        $this->template_404 = $templates['404'] ?? self::TEMPLATE_404;
        $this->template_error = $templates['error'] ?? self::TEMPLATE_ERROR;
        $this->template_debug = $templates['debug'] ?? self::TEMPLATE_DEBUG;
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