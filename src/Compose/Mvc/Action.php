<?php
/**
 * Created by PhpStorm.
 * User: Alamin
 * Date: 2016-10-16
 * Time: 9:22 PM
 */

namespace Compose\Mvc;


use Compose\Common\ServiceContainerAwareInterface;
use Interop\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

abstract class Action implements ServiceContainerAwareInterface
{
    use ResponseHelperTrait;

    protected
        /**
         * @var ContainerInterface
         */
        $container,

        /**
         * @var ServerRequestInterface  server request for the action
         */
        $request,

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
     */
    public function setServiceContainer(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @return ContainerInterface
     */
    public function getServiceContainer() : ContainerInterface
    {
        return $this->container;
    }

    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @param callable $next
     * @return ResponseInterface
     */
    final public function __invoke(ServerRequestInterface $request, ResponseInterface $response, callable $next = null)
    {
        $this->request = $request;
        $this->response = $response;
        $this->next = $next;

        try {
            $this->onInit();

            // will overwrite current response with the provided one
            $response = $this->execute($request);

            $this->response = $response;

            $this->onExit();

            return $response;

        } catch(\Exception $exception) {
            $this->onException($exception);
        }
    }

    /**
     * Handles the action
     *
     * @param ServerRequestInterface $request
     * @return mixed
     * @throws \Exception
     */
    public function execute(ServerRequestInterface $request) : ResponseInterface
    {
        $httpMethod = strtoupper($request->getMethod());
        $executeMethod = "execute__{$httpMethod}";

        if(!method_exists($this, $executeMethod)) {
            throw new \Exception("$executeMethod not implemented.");
        }

        return $this->{$executeMethod}($request);
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
}
