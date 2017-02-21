<?php
/**
 * Created by PhpStorm.
 * User: Alamin
 */

namespace Compose\Support;


use Compose\Adapter\League\PlatesViewRenderer;
use Compose\Adapter\Zend\Configuration;
use Compose\Mvc\ViewRendererInterface;
use Compose\Support\Error\ErrorResponseGenerator;
use Compose\System\ConfigurationInterface;
use Compose\System\Container\CompositeContainer;
use Compose\System\Container\ServiceContainer;
use Interop\Container\ContainerInterface;
use Zend\Diactoros\Response;
use Zend\Stratigility\Middleware\ErrorHandler;

/**
 * Class ContainerFactory
 * @package Compose\Support
 */
class ContainerFactory
{
    /**
     * @param array $config
     * @param ContainerInterface|null $defaultContainer
     * @return CompositeContainer
     */
    public function __invoke(array $config, ContainerInterface $defaultContainer = null)
    {
        // create all containers
        $container = new CompositeContainer();
        $serviceContainer = new ServiceContainer();
        $configContainer = new ArrayContainer($config);

        // start added containers in priority order
        if($defaultContainer) $container->addDelegate($defaultContainer); // should be high priority
        $container->addDelegate($serviceContainer);
        $container->addDelegate($configContainer);

        // configure service container
        $serviceContainer->set(ConfigurationInterface::class, new Configuration($config, false));
        $this->configureServices($serviceContainer, $config);

        // finally returns the composite container
        return $container;
    }

    /**
     * Convenient method
     *
     * @param array $config
     * @param ContainerInterface|null $defaultContainer
     * @return ContainerInterface
     */
    static public function createFromConfig(array $config, ContainerInterface $defaultContainer = null) : ContainerInterface
    {
        return (new self())($config, $defaultContainer);
    }


    /**
     * @param ServiceContainer $container
     * @param array $config
     */
    protected function configureServices(ServiceContainer $container, array $config) : void
    {
        $container->set(ViewRendererInterface::class, PlatesViewRenderer::class);
        $container->set(ErrorHandler::class, function(ContainerInterface $container) {
            return new ErrorHandler(
                new Response(),
                $container->get(ErrorResponseGenerator::class)
            );
        });
    }
}