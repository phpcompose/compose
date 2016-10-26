<?php
/**
 * Created by PhpStorm.
 * User: Alamin
 * Date: 2016-10-16
 * Time: 9:22 PM
 */

namespace Compose\Express;


use Compose\Common\ServiceInjector;
use Compose\Standard\Container\{ContainerAwareInterface, ServiceAwareInterface};
use Compose\Standard\Http\MiddlewareInterface;
use Interop\Container\ContainerInterface;
use Psr\Http\Message\{ResponseInterface, ServerRequestInterface};
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
     * @param ServerRequestInterface $request
     * @return mixed
     * @throws \HttpRequestException
     */
    public function forward(ServerRequestInterface $request)
    {
        $method = $this->generateHandlerMethodName($request);
        $reflection = new \ReflectionMethod($this, $method);
        if(!$reflection->isPublic()) {
            // only public methods are allowed for action handler
            throw new \ReflectionException();
        }

        // build action handler method params
        $params = $this->buildHandlerMethodParams($request);

        /** @var ServiceInjector $injector */
        $injector = $this->getContainer()->get(ServiceInjector::class);
        $injector->validateParameters($reflection, $params);

        return $reflection->invokeArgs($this, $params);
    }

    /**
     * @param ServerRequestInterface $request
     * @return string
     */
    protected function generateHandlerMethodName(ServerRequestInterface $request) : string
    {
        return strtolower($request->getMethod());
    }

    /**
     * @param ServerRequestInterface $request
     * @return array
     */
    protected function buildHandlerMethodParams(ServerRequestInterface $request) : array
    {
        /** @var RouteResult $route */
        $route = $request->getAttribute(RouteResult::class);
        $matchedParams = $route->getMatchedParams();
        if(count($matchedParams)) {
            $paramstring = reset($matchedParams);
            $params = explode('/', $paramstring);
        } else {
            $params = [];
        }

        // now add the ServerRequestInterface in the beginning of the method param
        array_unshift($params, $request);

        return $params;
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
     */
    public function view(string $template, array $data = [],  int $status = 200, array $headers = []) : ResponseInterface
    {
        /** @var \Zend\Expressive\Template\TemplateRendererInterface $renderer */
        $renderer = $this->getContainer()->get(\Zend\Expressive\Template\TemplateRendererInterface::class);
        if(!$renderer) {
            throw new \Exception("TemplateRendererInterface not found in the container.");
        }

        return $this->html($renderer->render($template, $data), $status, $headers);
    }
}
