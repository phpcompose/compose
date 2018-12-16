<?php
/**
 * Created by PhpStorm.
 * User: alaminahmed
 * Date: 2017-11-30
 * Time: 8:58 AM
 */

namespace Compose\Mvc;


use Compose\Container\ResolvableInterface;
use Compose\Container\ServiceResolver;
use Compose\Support\Configuration;
use Compose\Support\Invocation;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\HtmlResponse;

/**
 * Class PagesHandler
 * @package Compose\Page
 */
class PagesHandler implements MiddlewareInterface, ResolvableInterface
{
    protected
        /**
         * @var string default page directory
         */
        $dir,

        /**
         * @var ServiceResolver
         */
        $resolver,

        /**
         * @var ViewRendererInterface
         */
        $renderer,

        /**
         * @var string
         */
        $defaultPage = 'index';

    /**
     * PagesHandler constructor.
     * @param Configuration $configuration
     * @param ViewRendererInterface $renderer
     */
    public function __construct(ServiceResolver $resolver, ViewRendererInterface $renderer)
    {
        $this->resolver = $resolver;
        $this->renderer = $renderer;
    }

    /**
     * @param string $dir
     */
    public function setDirectory(string $dir)
    {
        $this->dir = $dir;
    }

    /**
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     * @throws \ReflectionException
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        // parse the request
        $data = null;
        $path = $request->getUri()->getPath();

        $templateInfo = $this->resolveTemplate($path);
        if(!$templateInfo) {
            return $handler->handle($request);
        }

        [$templateScript, $templateParams] = $templateInfo;

        // execute the code-behind script
        $codeScript = "{$templateScript}.php";
        if(file_exists($codeScript)) {
            $return = include $codeScript;

            // call if callable is returned
            if(is_callable($return)) {
                $invocation = new Invocation($return);
                if($invocation->getArgumentTypeAtIndex(0) == ServerRequestInterface::class) {
                    array_unshift($templateParams, $request); // add the request as the first param
                }
                $data = $invocation(...$templateParams);
            } else {
                $data = $return;
            }
        }

        return new HtmlResponse(
            $this->renderer->render($templateScript, $data)
        );
    }

    /**
     * Attempt to resolve the page from the page directory
     *
     * @param string $page
     * @return null|array
     */
    protected function resolveTemplate(string $page) : ?array
    {
        $dir = $this->dir;
        if(!$dir) {
            return null;
        }

        $params = [];
        $template = null;

        // normalizing
        $dir = rtrim($dir, '/');
        $page = trim($page, '/');

        $parts = explode('/', $page);
        while(count($parts)) {
            $path = $dir . '/' . implode('/', $parts);
            if(is_dir($path)) { // first check if
                $template = "{$path}/{$this->defaultPage}.phtml";
            } else {
                $template = "{$path}.phtml";
            }

            if(file_exists($template)) {
                break;
            } else {
                $template = null; // reset
            }

            // build the params
            array_unshift( $params, array_pop($parts));
        }

        if(!$template) {
            return null;
        }

        return [$template, $params];
    }
}