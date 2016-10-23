<?php
/**
 * Created by PhpStorm.
 * User: Alamin
 */

namespace Compose\Common;

use Zend\ServiceManager\Factory\AbstractFactoryInterface;
use Interop\Container\ContainerInterface;

/**
 * Class ServiceFactory
 *
 * Zend Expressive supports and highly recommends to provide explicit factory for all services/middlewares
 * However if your action middleware simply needs some dependencies to be injected to the constructor,
 * This factory will attempt to auto satisfy those dependencies.
 * @package Ats\Support
 */
class ServiceFactory implements AbstractFactoryInterface
{
    /**
     * Any classes implementing Ats\Support\ServiceAwareInterface will be attempted to injected with dependencies
     * using the service container
     *
     * @param ContainerInterface $container
     * @param string $requestedName
     * @return bool
     */
    public function canCreate(ContainerInterface $container, $requestedName)
    {
        return (class_exists($requestedName)
            && in_array(ServiceAwareInterface::class, class_implements($requestedName)));
    }

    /**
     * Uses reflection to perform simple iteration over constructor dependencies.
     *
     * @param ContainerInterface $container
     * @param string $requestedName
     * @param array|null $options
     * @return object
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        /** @var ServiceInjector $injector */
        $injector = $container->get(ServiceInjector::class);
        $instance = $injector->instantiate($requestedName);

        // if the instance also implements ServiceContainerAwareInterface interface,
        // then we will inject the container to the instance as well.
        if ($instance instanceof ServiceContainerAwareInterface) {
            $instance->setServiceContainer($container);
        }

        return $instance;
    }
}
