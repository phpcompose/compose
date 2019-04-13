<?php
/**
 * Created by PhpStorm.
 * User: alaminahmed
 * Date: 2017-11-30
 * Time: 8:58 AM
 */

namespace Compose\Mvc;


use Compose\Container\ContainerAwareInterface;
use Compose\Container\ContainerAwareTrait;
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
class PagesHandler implements MiddlewareInterface, ContainerAwareInterface
{
    use ContainerAwareTrait;

    protected
        /**
         * @var string default page directory
         */
        $dir,

        $folders = [],

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
    public function __construct(ViewRendererInterface $renderer)
    {
        $this->renderer = $renderer;
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
     * Map specific url path to a folder
     * @param string $page
     * @param string $dir
     */
    public function addFolder(string $path, string $dir)
    {
        $this->folders[$path] = $dir;
    }

    /**
     * Set path mapping folder.  Existing maps will be replaced
     * 
     * @param array $folders
     */
    public function setFolders(array $folders) 
    {
        $this->folders = $folders;
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
                if(is_object($return) && $return instanceof ContainerAwareInterface) {
                    $return->setContainer($this->getContainer());
                }

                $invocation = new Invocation($return);
                if($invocation->getArgumentTypeAtIndex(0) == ServerRequestInterface::class) {
                    array_unshift($templateParams, $request); // add the request as the first param
                }
                $data = $invocation(...$templateParams);
            }  else {
                $data = $return;
            }
        }

        if($data instanceof ResponseInterface) {
            return $data;
        }

        return new HtmlResponse(
            $this->renderer->render(new View($templateScript, $data), $request)
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
        $folders = $this->folders;

        $params = [];
        $template = null;

        // normalizing
        $dir = rtrim($dir, '/');
        $page = trim($page, '/');

        $parts = explode('/', $page);
      
        // check if path to folder mapping available
        if(isset($folders[$parts[0]])) {
            $foldername = \array_shift($parts);
            $dirname = $folders[$foldername];
        } else {
            $dirname = $dir;
        }

        while(count($parts)) {
            $path = $dirname . '/' . implode('/', $parts);
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