<?php
/**
 * Created by PhpStorm.
 * User: Alamin
 * Date: 2016-10-16
 * Time: 9:22 PM
 */

namespace Compose\Express;


use Compose\Common\Invocation;
use Compose\System\Container\{
    ContainerAwareInterface,
    ContainerAwareTrait,
    ServiceAwareInterface
};
use Compose\System\Http\CommandInterface;
use Compose\System\Http\MiddlewareInterface;
use Psr\Http\Message\{
    ResponseInterface, ServerRequestInterface
};

abstract class Action
    implements MiddlewareInterface, CommandInterface, ServiceAwareInterface , ContainerAwareInterface
{
    use ActionResolverTrait, ResponseHelperTrait, ContainerAwareTrait;

    protected
        /**
         * @var ServerRequestInterface  server request for the action
         */
        $request,

        /**
         * @var ResponseInterface server response for the action
         */
        $response;


    /**
     * @inheritdoc
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @return ResponseInterface
     */
    final public function __invoke(ServerRequestInterface $request, ResponseInterface $response, callable $next = null) : ResponseInterface
    {
        $this->request = $request;
        $this->response = $response;

        try {
            $this->onInit();

            $response = $this->execute($request);

            $this->response = $response;

            $this->onExit();

        } catch(\Exception $exception) {
            $this->onException($exception);
        }

        return $this->response;
    }

    /**
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function execute(ServerRequestInterface $request) : ResponseInterface
    {
        /** @var Invocation $invocation */
        $invocation = $this->resolveActionHandler($request);
        return $invocation();
    }

    /**
     * Initialize
     */
    protected function onInit()
    {}

    /**
     *
     */
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
    protected function view(string $template, array $data = [],  int $status = 200, array $headers = []) : ResponseInterface
    {
        /** @var \Zend\Expressive\Template\TemplateRendererInterface $renderer */
        $renderer = $this->getContainer()->get(\Zend\Expressive\Template\TemplateRendererInterface::class);
        if(!$renderer) {
            throw new \Exception("TemplateRendererInterface not found in the container.");
        }

        return $this->html($renderer->render($template, $data), $status, $headers);
    }
}
