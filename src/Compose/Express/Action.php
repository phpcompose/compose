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
use Psr\Http\Message\{
    ResponseInterface,
    ServerRequestInterface
};

abstract class Action
    implements CommandInterface, ServiceAwareInterface , ContainerAwareInterface
{
    use ActionResolverTrait, ResponseHelperTrait, ContainerAwareTrait;

    protected
        /**
         * @var ServerRequestInterface  server request for the action
         */
        $request;


    /**
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    final public function process(ServerRequestInterface $request) : ResponseInterface
    {
        $this->request = $request;

        try {
            $this->onInit();

            $response = $this->onProcess($request);

            $this->onExit();

        } catch(\Exception $exception) {
            $this->onException($exception);
        }

        return $response;
    }

    /**
     * Initialize
     */
    protected function onInit()
    {}

    /**
     * @param ServerRequestInterface $request
     * @return mixed
     */
    protected function onProcess(ServerRequestInterface $request)
    {
        /** @var Invocation $invocation */
        $invocation = $this->resolveActionHandler($request);
        return $invocation();
    }

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
