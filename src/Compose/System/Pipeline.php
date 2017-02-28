<?php
/**
 * Created by PhpStorm.
 * User: Alamin
 */

namespace Compose\System\Http;


use Interop\Container\ContainerInterface;
use Interop\Http\ServerMiddleware\DelegateInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Stratigility\MiddlewarePipe;

/**
 * Class Pipeline
 *
 * Provides application/system pipeline to boot application
 * @package Compose\System
 */
class Pipeline extends MiddlewarePipe
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
    }

    /**
     * @return ContainerInterface
     */
    public function getContainer() : ContainerInterface
    {
        return $this->container;
    }


    public function process(ServerRequestInterface $request, DelegateInterface $delegate) : ResponseInterface
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