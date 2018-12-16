<?php
namespace Compose\Container;


use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

/**
 * Class ServiceContainer
 */
class ServiceContainer implements ContainerInterface
{
    protected
        /**
         * @var array
         */
        $instances = [],

        /**
         * @var array
         */
        $services = [];

    /**
     * ServiceContainer constructor.
     */
    public function __construct()
    {
        $resolver = new ServiceResolver($this);
        $this->set(ServiceResolver::class, $resolver);
    }

    /**
     * @return ServiceResolver
     * @throws \Exception
     */
    public function getResolver() : ServiceResolver
    {
        return $this->get(ServiceResolver::class);
    }

    /**
     * @param string $id
     * @return bool
     * @throws \Exception
     */
    public function has($id) : bool
    {
        return isset($this->instances[$id]) ||      // instance available
            isset($this->services[$id]) ||          // service available
            $this->getResolver()->isService($id);   // able to autowire
    }

    /**
     * @param string $id
     * @return mixed|object
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     * @throws \Exception
     */
    public function get($id)
    {
        if(isset($this->instances[$id])) { // service instance, if available
            return $this->instances[$id];
        }

        $service = $this->services[$id] ?? null;
        if($service) { // service custom factory, if available
            if(is_string($service)) { // check for alias
                $instance = $this->get($service); // recursive call to resolve aliases
            } else {
                $instance = $this->resolve($service);
            }
        } else {
            $service = $id;
            $instance = $this->resolve($service);
        }


        $this->instances[$id] = $instance;
        return $instance;
    }

    /**
     * resolve service
     *
     * Will check service interface implementation validation only when random arbitrary class is being requested to be resolved.
     * However this validation will not happen if any class is explicitly assigned by set method
     *
     * @param $id
     * @return mixed|null|object
     * @throws NotFoundException
     * @throws NotFoundExceptionInterface
     * @throws \Exception
     * @throws \Psr\Container\ContainerExceptionInterface
     */
    public function resolve($service, array $args = null)
    {
        $resolver = $this->getResolver();

        if(is_callable($service)) { // callable factory
            $instance = call_user_func($service, $this, $service);
        } else if(is_string($service)) {
            if(!$resolver->isService($service)) {
                throw new NotFoundException("Service class: {$service} must implement ResolvableInterface or be registered as service.");
            }
            $instance = $resolver->instantiate($service, $args);
        } else if(is_object($service)) { // impossible, will be set to instance by set method
            $instance = $service;
        } else { // this is impossible since it will be cought be set method
            throw new \LogicException("Unable to resolve");
        }

        return $instance;
    }

    /**
     * Add service to the container
     *
     * A $service can be object, or fully qualified class name.
     *
     * Supported services type will be validated and thrown error
     * @param $id
     * @param mixed $service
     */
    public function set(string $id, $service = null)
    {
        if(isset($this->instances[$id]) || isset($this->services[$id])) {
            throw new \LogicException("Service Instance already available for: {$id}");
        }

        if(is_null($service) || is_callable($service) || is_string($service)) {
            $this->services[$id] = $service;
        } else if(is_object($service)) { // any object other then closure is instance
            $this->instances[$id] = $service;
        } else {
            throw new \LogicException("Cannot add service: {$id}.  Not supported type.");
        }
    }

    /**
     * @param array $services
     */
    public function setMany(array $services)
    {
        foreach($services as $id => $service) {
            $this->set($id, $service);
        }
    }
}
