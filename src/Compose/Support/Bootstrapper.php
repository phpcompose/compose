<?php
/**
 * Created by PhpStorm.
 * User: Alamin
 */

namespace Compose\Support;


use Compose\Adapter\League\PlatesViewRenderer;
use Compose\Mvc\ViewRendererInterface;
use Compose\Support\Error\ViewResponseGenerator;
use Interop\Container\ContainerInterface;
use Interop\Http\Middleware\DelegateInterface;
use Interop\Http\Middleware\ServerMiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response;
use Zend\ServiceManager\ServiceManager;
use Zend\Stratigility\Middleware\ErrorHandler;

class Bootstrapper implements ServerMiddlewareInterface
{
    protected
        $container;

    /**
     * Bootstrapper constructor.
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function loadContainer()
    {

    }

    public function loadApplicationPipeline()
    {

    }



    public function process(ServerRequestInterface $request, DelegateInterface $delegate)
    {
        return $delegate->process($request);
    }


    static public function bootstrap(ContainerInterface $container)
    {

    }
}