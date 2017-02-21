<?php
/**
 * Created by PhpStorm.
 * User: Alamin
 */

namespace Compose\System\Http;


use Interop\Container\ContainerInterface;
use Interop\Http\Middleware\DelegateInterface;
use Interop\Http\Middleware\ServerMiddlewareInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Stratigility\MiddlewarePipe;

/**
 * Class Pipeline
 *
 * Provides application/system pipeline to boot application
 * @package Compose\System
 */
class Pipeline extends MiddlewarePipe implements ServerMiddlewareInterface
{
    protected
        /**
         * @var ContainerInterface
         */
        $container;

    public function __construct(ContainerInterface $container)
    {
        parent::__construct();
        $this->container = $container;
        $this->onInit();
    }


    /**
     * Initialize the pipeline
     *
     * This is called as part of construction
     */
    protected function onInit() : void
    {
        $this->raiseThrowables();
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
     * @param callable|null $out
     * @return ResponseInterface
     */
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, callable $out = null)
    {
        $this->preProcess($request);
        $response = parent::__invoke($request, $response, $out);
        $this->postProcess($response);

        return $response;
    }

    /**
     *
     * @param ServerRequestInterface $request
     * @param DelegateInterface $delegate
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, DelegateInterface $delegate)
    {
        $this->preProcess($request);
        $response = parent::process($request, $delegate);
        $this->postProcess($response);

        return $response;
    }


    /**
     * Called before middleware process starts
     *
     * @param ServerRequestInterface $request
     */
    protected function preProcess(ServerRequestInterface $request) : void
    {
    }

    /**
     * Called after middleware process ends
     *
     * @param ResponseInterface $response
     */
    protected function postProcess(ResponseInterface $response) : void
    {
    }
}