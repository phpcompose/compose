<?php
/**
 * Created by PhpStorm.
 * User: Alamin
 */
namespace Compose\System\Container;


use Interop\Container\ContainerInterface;

/**
 * Class DependencyResolver
 *
 * Uses given ContainerInterface to resolve dependencies
 * @package Ats\Support\Container
 */
class DependencyResolver
{
    protected
        /** @var ContainerInterface */
        $container;

    /**
     * DependencyResolver constructor.
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * Invokes given method on the reflecting object
     *
     * @param callable $callable
     * @param array|null $args
     * @return mixed
     */
    public function invoke(callable $callable, array $args = [])
    {
        $reflection = $this->reflectCallable($callable);

        // attempt to resolve dependencies
        $dependencies = $this->resolveFunctionDependencies($reflection, $args);
        return $reflection->invokeArgs($dependencies);
    }

    /**
     * Attempt to get reflection for given callable
     *
     * @param callable $callable
     * @return \ReflectionFunctionAbstract
     * @throws \ReflectionException
     */
    protected function reflectCallable(callable $callable) : \ReflectionFunctionAbstract
    {
        if (is_string($callable) || $callable instanceof \Closure) {
            $reflection = new \ReflectionFunction($callable);
        } elseif(is_array($callable) && count($callable) == 2) { // standard php [class, method] syntax
            list($class, $method) = $callable;
            $reflection = new \ReflectionMethod($class, $method);
            $reflection->setAccessible(true); // allows to call private/protected methods
        } else {
            throw new \ReflectionException("Unable to reflect callable.");
        }

        return $reflection;
    }

    /**
     * @param $className
     * @param array|null $args
     * @return null|object
     * @internal param ContainerInterface $container
     */
    public function instantiate($className, array $args = [])
    {
        $instance = null;
        $reflection = new \ReflectionClass($className);

        $constructor = $reflection->getConstructor();
        if ($constructor === null) {
            // if construction is not used, simply create new object without constructor
            $instance = $reflection->newInstanceWithoutConstructor();
        } else {
            // attempt to inject dependencies for the constructor and then instantiate
            $dependencies = $this->resolveFunctionDependencies($constructor, $args);
            $instance = $reflection->newInstanceArgs($dependencies);
        }

        return $instance;
    }

    /**
     * Attempts to resolve any function parameters using provided container
     *
     * @param \ReflectionFunctionAbstract $function
     * @param array $args
     * @return array
     * @throws \InvalidArgumentException
     * @internal param ContainerInterface $container
     */
    public function resolveFunctionDependencies(\ReflectionFunctionAbstract $function,  array $args = [] ) : array
    {
        $container = $this->container;

        // analyze constructor params and build up the dependencies
        $params = $function->getParameters();
        $dependencies = [];
        foreach ($params as $parameter) {
            $paramName = ($parameter->getClass()) ? $parameter->getClass()->getName() : $parameter->getName();

            // first check if passed $args has the param name,
            // if so we give this one priority
            if(isset($args[$paramName])) {
                $dependencies[] = $args[$paramName];
                continue;
            }

            // if now check if container has it
            if ($container->has($paramName)) {
                $dependencies[] = $container->get($paramName);
                continue;
            }

            // if dependencies still not resolved,
            // check if it is optional
            if ($parameter->isOptional()) {
                $dependencies[] = $parameter->getDefaultValue();
                continue;
            }

            // unable to resolve required params,
            // this is an error
            throw new \InvalidArgumentException("Unable to resolve param: {$paramName} of type: {$parameter->getType()}");
        }

        return $dependencies;
    }
}