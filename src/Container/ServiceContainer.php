<?php
namespace Compose\Container;


use Exception;
use LogicException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use ReflectionException;

/**
 * Class ServiceContainer
 */
class ServiceContainer implements ContainerInterface
{
    protected array $aliases = [];
    protected array $instances = [];
    protected array $services = [];

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
     * @throws Exception
     */
    public function getResolver() : ServiceResolver
    {
        return $this->get(ServiceResolver::class);
    }

    /**
     * @param string $id
     * @return bool
     * @throws Exception
     */
    public function has(string $id) : bool
    {
        return isset($this->instances[$id]) ||      // instance available
            isset($this->services[$id]) ||          // service available
            $this->getResolver()->isService($id);   // able to auto-wire
    }

    /**
     * Attempts to get service from given $id.
     * Service will be cached for next use
     * @todo Better logic organization
     * @param string $id
     * @return mixed
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws Exception
     */
    public function get(string $id): mixed
    {
        if(isset($this->instances[$id])) { // service instance, if available
            return $this->instances[$id];
        }

        $service = $this->services[$id] ?? null;
        if($service) { // service custom factory, if available
            if(is_string($service) && $id !== $service) { // check for alias recursively :(
                $instance = $this->get($service);
            } else {
                $instance = $this->resolve($service);
            }
        } else {
            // this could be for few request
            // the class implements ResolvableInterface
            // or manually registered itself as service
            $service = $id;
            if(!$this->getResolver()->isService($service)) {
                throw new NotFoundException("Service class: $service must implement ResolvableInterface or be registered as service.");
            }
            $instance = $this->resolve($service);
        }


        $this->instances[$id] = $instance;
        return $instance;
    }

    /**
     * resolve service
     *
     * Will check service interface implementation validation only when random arbitrary class is being requested to be resolved.
     * However, this validation will not happen if any class is explicitly assigned by set method
     *
     * @param $service
     * @param array|null $args
     * @return mixed
     * @throws ReflectionException
     * @throws Exception
     */
    public function resolve($service, array $args = null): mixed
    {
        $resolver = $this->getResolver();

        if(is_callable($service)) { // callable factory
            $instance = call_user_func($service, $this, $service);
        } else if(is_string($service)) {
            $instance = $resolver->instantiate($service, $args);
        } else if(is_object($service)) { // impossible, will be set to instance by set method
            $instance = $service;
        } else { // this is impossible since it will be caught be set method
            throw new LogicException("Unable to resolve");
        }

        return $instance;
    }

    /**
     * Add service to the container
     *
     * A $service can be an object, or fully qualified class name.
     *
     * Supported services type will be validated and thrown error
     * @param string $id
     * @param mixed $service
     */
    public function set(string $id, mixed $service = null)
    {
        if(isset($this->instances[$id]) || isset($this->services[$id])) {
            throw new LogicException("Service Instance already available for: $id");
        }

        if(is_null($service) || $service === $id) {
            $this->services[$id] = $id;
        } else if(is_callable($service) || is_string($service)) {
            $this->services[$id] = $service;
        } else if(is_object($service)) { // any object other than closure is instanced
            $this->instances[$id] = $service;
        } else {
            throw new LogicException("Cannot add service: $id.  Not supported type.");
        }
    }

    /**
     * @param array $services
     */
    public function setMany(array $services)
    {
        foreach($services as $id => $service) {
            if(is_integer($id)) {
                $id = $service;
            }
            $this->set($id, $service);
        }
    }
}
