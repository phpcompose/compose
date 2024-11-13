<?php
namespace Compose\App;

use Compose\Container\ResolvableInterface;
use League\Plates\Engine;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Compose\Support\Invocation;
use Compose\Container\ContainerAwareInterface;
use Compose\Container\ContainerAwareTrait;
use Exception;
use Laminas\Diactoros\Response\HtmlResponse;

/**
 * 
 */
class PagesMiddleware implements MiddlewareInterface, ContainerAwareInterface, ResolvableInterface
{
    use ContainerAwareTrait;

    protected Engine $engine;

    public function __construct(TemplateRenderer $renderer)
    {
        $this->engine = $renderer->getEngine();   
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $engine = $this->engine;
        $path = $request->getUri()->getPath();
        $page = trim($path, '/');
        $paths = explode('/', $page);
        $params = [];
        $template = null;
        $data = [];

        // resolve template
        while(count($paths)) {
            $template = implode('/', $paths);
            if($engine->exists($template)) {
                // short-circuit
                break;
            } 
            $template = null; // reset

            // build the params
            array_unshift( $params, array_pop($paths));
        }

        if($template) {
            // page found
            // check if code exists
            $templateScript = $engine->path($template);
            $templateCode = "{$templateScript}.php";
            if(file_exists($templateCode)) {
                $data = $this->executeCodeScript($templateCode, $params, $request);
            }

            return new HtmlResponse(
                $engine->render($template, $data)
            );
        }

        return $handler->handle($request);
    }



    protected function executeCodeScript(string $script, array $params, ServerRequestInterface  $request) : mixed 
    {
        $return = include $script;
        // call if callable is returned
        if(is_callable($return)) {
            if(is_object($return) && $return instanceof ContainerAwareInterface) {
                $return->setContainer($this->getContainer());
            }

            $invocation = new Invocation($return);
            array_unshift($params, $request); // add the Request in the first arguemnt
            $data = $invocation(...$params);
        }  else if(is_array($return)) {
            $data = $return;
        } else {
            throw new Exception("Invalid Code for page: " . $script);
        }
        return $data;
    }
}