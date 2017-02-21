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
        TEMPLATE_ERROR_KEY = 'http_error',
        TEMPLATE_404_KEY = 'http_404',
        TEMPLATE_DEBUG_KEY = 'debug';


    protected
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
        $this->template_404 = $templates[self::TEMPLATE_404_KEY] ?? null;
        $this->template_error = $templates[self::TEMPLATE_ERROR_KEY] ?? null;
        $this->template_debug = $templates[self::TEMPLATE_DEBUG_KEY] ?? $this->getDebugTemplate();
    }

    /**
     * @return string
     */
    protected function getDebugTemplate() : string
    {
        // @todo must be better way to find path relative to vendor src dir.
        $script = "templates/error/debug";

        return $script;
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
        if(!$this->debug && (!$this->template_404 || !$this->template_error)) {
            throw new \Exception(sprintf("%s needs templates array with following keys: %s, %s",
                get_class($this),
                self::TEMPLATE_ERROR_KEY,
                self::TEMPLATE_404_KEY));
        }

        if($this->debug) {
            $template = $this->getDebugTemplate();
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