<?php
/**
 * Created by PhpStorm.
 * User: Alamin
 * Date: 2016-10-18
 * Time: 6:58 PM
 */

namespace Compose\Common;
use Interop\Container\ContainerInterface;


class ServiceInjector extends Reflection
{
    protected
        /** @var ContainerInterface */
        $container;

    /**
     * @param ContainerInterface $container
     * @inheritdoc
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * Invokes given method on the reflecting object
     *
     * @param callable $method
     * @param array|null $args
     * @return mixed
     */
    public function invoke(callable $method, array $args = null)
    {
        $methodReflector = $this->reflectCallable($method);
        $methodReflector->setAccessible(true); // allows to call private/protected methods


        // finally invoking the methods
        $dependencies = $this->resolveFunctionDependencies($methodReflector, $args);
        return $methodReflector->invokeArgs($dependencies);
    }

    /**
     * @param $classname
     * @param array|null $args
     * @return null|object
     * @internal param ContainerInterface $container
     */
    public function instantiate($classname, array $args = [])
    {
        $instance = null;
        $reflection = new \ReflectionClass($classname);

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
    public function resolveFunctionDependencies(\ReflectionFunctionAbstract $function,  array $args = [] )
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