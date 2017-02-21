<?php
/**
 * Created by PhpStorm.
 * User: Alamin
 */

namespace Compose\System\Container;


use Interop\Container\ContainerInterface;
use Interop\Container\Exception\NotFoundException;

/**
 * Class ServiceContainer
 *
 * @package Ats\System\Container
 */
class ServiceContainer implements ContainerInterface, DelegateContainerInterface
{
    protected
        /**
         * @var DependencyResolver
         */
        $resolver,

        /**
         * @var ContainerInterface
         */
        $defaultContainer = null,

        /**
         * @var array
         */
        $instances = [],

        /**
         * @var array
         */
        $services = [];


    /**
     * Implements the DelegateContainerInterface
     *
     * @param ContainerInterface $container
     */
    public function setContainer(ContainerInterface $container)
    {
        $this->defaultContainer = $container;
    }

    /**
     * @return ContainerInterface
     */
    public function getContainer() : ContainerInterface
    {
        return $this->defaultContainer ?? $this;
    }

    /**
     * @return DependencyResolver
     */
    public function getResolver() : DependencyResolver
    {
        if(!$this->resolver) {
            $this->resolver = new DependencyResolver($this->getContainer());
        }

        return $this->resolver;
    }

    /**
     * @param string $id
     * @return bool
     */
    public function has($id) : bool
    {
        return isset($this->instances[$id]) ||
            isset($this->services[$id]) ||
            $this->doesImplement($id, ServiceInterface::class);
    }

    /**
     * @param string $id
     * @return mixed|object
     */
    public function get($id)
    {
        if(isset($this->instances[$id])) { // service instance, if available
            return $this->instances[$id];
        }

        $container = $this->getContainer(); // get the main container
        if($this->services[$id]) { // service custom factory, if available
            $service = $this->services[$id];
            if($service instanceof \Closure) {
                return $this->instances[$id] = $service($container, $id);
            } else { // class name
                return $this->get($service);
            }
        }

        // finally attempt to autowire
        return $this->autowire($container, $id);
    }

    /**
     * @param $id
     * @param mixed $service
     */
    public function set($id, $service)
    {
        if($this->instances[$id]) {
            throw new \LogicException("Service Instance already available for: {$id}");
        }

        if(is_object($service) && !$service instanceof \Closure) { // any object other then closure is instance
            $this->instances[$id] = $service;
        } else if($service instanceof \Closure) {
            $this->services[$id] = $service;
        } else if(is_string($service) && class_exists($service)) {
            $this->services[$id] = $service;
        } else {
            throw new \LogicException("Cannot add service: {$id}.  Not supported type.");
        }
    }

    /**
     * Attempts to autowire requested service using the container by resolving dependencies.
     *
     * @param ContainerInterface $container
     * @param string $requestedName
     * @return object
     */
    protected function autowire(ContainerInterface $container, $requestedName)
    {
        if($this->doesImplement($requestedName, ServiceAwareInterface::class)) {
            // we will attempt to resolve dependencies and instantiate the object
            $instance = $this->getResolver()->instantiate($requestedName);
        } else {
            $instance = new $requestedName();
        }

        if(!$instance) {
            throw new class("Unable to instantiate service: {$requestedName}.") extends \Exception implements NotFoundException {};
        }

        // if the instance also implements ServiceContainerAwareInterface interface,
        // then we will inject the container to the instance as well.
        if ($instance instanceof ContainerAwareInterface) {
            $instance->setContainer($container);
        }

        return $instance;
    }

    /**
     * Checks to see if given class/service implement one of the interface supported by the container
     *
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
