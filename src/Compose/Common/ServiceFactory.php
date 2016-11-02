<?php
/**
 * Created by PhpStorm.
 * User: Alamin
 */

namespace Compose\Common;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\AbstractFactoryInterface;
use Zend\Expressive\Container\Exception\NotFoundException;
use Compose\Standard\Container;




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
     * @param string $name
     * @return bool
     */
    public function canCreate(ContainerInterface $container, $name)
    {
        return $this->doesImplement($name, Container\ServiceInterface::class);
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

        if($this->doesImplement($requestedName, Container\ServiceFactoryInterface::class)) {
            $instance = $requestedName::create($container);
        }
        else if($this->doesImplement($requestedName, Container\ServiceAwareInterface::class)) {
            // we will attempt to reoslve dependencies and instantiate the object
            /** @var ServiceInjector $injector */
            $injector = $container->get(ServiceInjector::class);
            $instance = $injector->instantiate($requestedName);
        }
        else {
            // @todo we can try to delegate this
            $instance = new $requestedName();
        }

        if(!$instance) {
            throw new NotFoundException("Unable to instantiate service: {$requestedName}.");
        }


        // if the instance also implements ServiceContainerAwareInterface interface,
        // then we will inject the container to the instance as well.
        if ($instance instanceof Container\ContainerAwareInterface) {
            $instance->setContainer($container);
        }

        return $instance;
    }



    /**
     * @param $class
     * @param $interface
     * @return bool
     */
    protected function doesImplement($class, $interface)
    {
        return (class_exists($class)
            && in_array($interface, class_implements($class)));
    }
}
