<?php
/**
 * Created by PhpStorm.
 * User: alaminahmed
 * Date: 2018-06-24
 * Time: 1:03 PM
 */

namespace Compose\Container;



use Compose\Support\Invocation;
use Psr\Container\ContainerInterface;

class ServiceResolver
{
    use ContainerAwareTrait;

    /**
     * ServiceResolver constructor.
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->setContainer($container);
    }

    /**
     * @param $class
     * @return bool
     */
    public function isService($class) : bool
    {
        return (class_exists($class)
            && in_array(ResolvableInterface::class, class_implements($class)));
    }

    /**
     * @param $resolvable
     * @param array|null $args
     * @return mixed|null|object
     * @throws \Exception
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     * @throws \ReflectionException
     */
    public function resolve($resolvable, array $args = null)
    {
        if(is_callable($resolvable)) {
            return $this->invoke($resolvable, $args);
        } else if(is_string($resolvable)) {
            return $this->instantiate($resolvable, $args);
        } else {
            throw new \Exception("Unable to resolve.");
        }
    }

    /**
     * Invokes given method on the reflecting object
     *
     * @param callable $callable
     * @param array|null $args
     * @return mixed
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     * @throws \ReflectionException
     */
    public function invoke(callable $callable, array $args = null)
    {
        $reflection = Invocation::reflectCallable($callable);

        // attempt to resolve dependencies
        $dependencies = $this->resolveFunctionDependencies($reflection, $args);
        return $reflection->invokeArgs($dependencies);
    }

    /**
     * @param $className
     * @param array|null $args
     * @return null|object
     * @throws \ReflectionException
     */
    public function instantiate($className, array $args = null)
    {
        $container = $this->getContainer();
        $instance = null;

        // if factory is provided use that
        if(isset(class_implements($className)[ServiceFactoryInterface::class])) {
            return $className::create($container, $className);
        }

        // use reflection to resolve and instantiate
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

        // if the instance also implements ContainerAwareInterface interface,
        // then we will inject the container to the instance as well.
        if ($instance instanceof ContainerAwareInterface) {
            $instance->setContainer($container);
        }

        return $instance;
    }

    /**
     * Attempts to resolve any function parameters using provided container
     *
     * @param \ReflectionFunctionAbstract $function
     * @param array $args
     * @return array
     * @throws \Exception
     */
    public function resolveFunctionDependencies(\ReflectionFunctionAbstract $function,  array $args = null ) : array
    {
        $container = $this->getContainer();
        $params = $function->getParameters();
        $dependencies = [];
        foreach ($params as $parameter) {
            $paramName = ($parameter->getClass()) ? $parameter->getClass()->getName() : $parameter->getName();

            if (isset($args[$paramName])) { // first check if passed $args has the param name,
                $dependencies[] = $args[$paramName];
            } else if ($container->has($paramName)) { // if now check if container has it
                $dependencies[] = $container->get($paramName);
            } else if ($parameter->isOptional()) { // check if it is optional
                $dependencies[] = $parameter->getDefaultValue();
            } else { // unable to resolve required params,
                throw new \InvalidArgumentException("Unable to resolve param: {$paramName} of type: {$parameter->getType()}");
            }
        }

        return $dependencies;
    }
}