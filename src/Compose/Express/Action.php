<?php
/**
 * Created by PhpStorm.
 * User: Alamin
 * Date: 2016-10-16
 * Time: 9:22 PM
 */

namespace Compose\Express;


use Compose\Standard\Container\ContainerAwareInterface;
use Compose\Standard\Container\ServiceAwareInterface;
use Compose\Standard\Http\MiddlewareInterface;
use Compose\Standard\Http\RestfulResourceInterface;
use Compose\Standard\Http\RestfulResourceTrait;
use Interop\Container\ContainerInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Expressive\Router\RouteResult;
use Zend\Expressive\Router\RouterInterface;

abstract class Action implements MiddlewareInterface, ServiceAwareInterface , ContainerAwareInterface
{
    use ResponseHelperTrait;

    protected
        /**
         * Allows ability to map
         * @var array
         */
        $httpMethodMap = [
            'get' => 'get',
            'put' => 'put',
            'post' => 'post',
            'delete' => 'delete'
        ],

        /**
         * @var ContainerInterface
         */
        $container,

        /**
         * @var ServerRequestInterface  server request for the action
         */
        $request,

        $router,

        /**
         * @var ResponseInterface server response for the action
         */
        $response,

        /**
         * @var callable $next
         */
        $next = null;

    /**
     * @param ContainerInterface $container
     * @return mixed|void
     */
    public function setContainer(ContainerInterface $container)
    {
        $this->container = $container;
        $this->router = $container->get(RouterInterface::class);
    }

    /**
     * @return ContainerInterface
     */
    public function getContainer() : ContainerInterface
    {
        return $this->container;
    }

    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @param callable $next
     * @return ResponseInterface
     */
    final public function __invoke(ServerRequestInterface $request, ResponseInterface $response, callable $next = null) : ResponseInterface
    {
        $this->request = $request;
        $this->response = $response;
        $this->next = $next;

        try {
            $this->onInit();

            $response = $this->forward($request);

            $this->response = $response;

            $this->onExit();

            return $response;

        } catch(\Exception $exception) {
            $this->onException($exception);
        }
    }

    /**
     * @param RequestInterface $request
     * @return mixed
     * @throws \HttpRequestException
     */
    public function forward(ServerRequestInterface $request)
    {
        /** @var RouteResult $route */
        $method = strtolower($request->getMethod());
        $reflection = new \ReflectionMethod($this, $method);
        if(!$reflection->isPublic()) {
            // only public methods are considered
            throw new \ReflectionException();
        }

        return $reflection->invoke($this, $request);
    }

    /**
     * Initialize
     */
    protected function onInit()
    {}

    protected function onExit()
    {}

    /**
     * Handle
     * @param \Exception $e
     * @throws \Exception
     */
    protected function onException(\Exception $e)
    {
        throw $e;
    }


    /**
     * Create additional Response helper method for view
     *
     * @param string $template
     * @param array $data
     * @param int $status
     * @param array $headers
     * @return ResponseInterface|\Zend\Diactoros\Response\HtmlResponse
     * @throws \Exception
     * @internal param $model
     */
    public function view(string $template, array $data = [],  int $status = 200, array $headers = []) : ResponseInterface
    {
        /** @var \Zend\Expressive\Template\TemplateRendererInterface $renderer */
        $renderer = $this->getContainer()->get(\Zend\Expressive\Template\TemplateRendererInterface::class);
        if(!$renderer) {
            throw new \Exception("TemplateRendererInterface not found in the container.");
        }

        // now need to guess template script based on current request
        // /app/some/path/write => App\WriteAction::class                           = app::write
        // /app/some/path/write => App\Action\HandlerAction::class                  = app::handler
        // /app/some/path/blog/read => Abc\Controller\BlogController::class:read    = abc::blog\read
        return $this->html($renderer->render($template, $data), $status, $headers);
    }
}
