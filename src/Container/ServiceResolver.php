<?php
/**
 * Created by PhpStorm.
 * User: alaminahmed
 * Date: 2018-06-24
 * Time: 1:03 PM
 */

namespace Compose\Container;

use Compose\Support\Invocation;
use Exception;
use ReflectionClass;
use ReflectionMethod;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use ReflectionException;
use ReflectionFunctionAbstract;
use Throwable;

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
     * @throws Exception
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws ReflectionException
     */
    public function resolve($resolvable, ?array $args = null)
    {
        try {
            if (is_callable($resolvable)) {
                return $this->invoke($resolvable, $args);
            }

            if (is_string($resolvable)) {
                return $this->instantiate($resolvable, $args);
            }

            throw new ContainerException(sprintf('Unable to resolve value of type "%s".', gettype($resolvable)));
        } catch (ContainerException $exception) {
            throw $exception;
        } catch (Throwable $exception) {
            throw ContainerException::fromResolution($this->describeResolvable($resolvable), $exception);
        }
    }

    /**
     * Invokes given method on the reflecting object
     *
     * @param callable $callable
     * @param array|null $args
     * @return mixed
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws ReflectionException
     */
    public function invoke(callable $callable, ?array $args = null)
    {
        try {
            $reflection = Invocation::reflectCallable($callable);

            $dependencies = $this->resolveFunctionDependencies($reflection, $args, $callable);
            return $reflection->invokeArgs($dependencies);
        } catch (ContainerException $exception) {
            throw $exception;
        } catch (Throwable $exception) {
            throw ContainerException::fromResolution($this->describeResolvable($callable), $exception);
        }
    }

    /**
     * @param $className
     * @param array|null $args
     * @return null|object
     * @throws ReflectionException
     */
    public function instantiate($className, ?array $args = null)
    {
        try {
            $container = $this->getContainer();
            $instance = null;

            if (isset(class_implements($className)[ServiceFactoryInterface::class])) {
                return $className::create($container, $className);
            }

            $reflection = new ReflectionClass($className);
            $constructor = $reflection->getConstructor();
            if ($constructor === null) {
                $instance = $reflection->newInstanceWithoutConstructor();
            } else {
                $dependencies = $this->resolveFunctionDependencies($constructor, $args, $className);
                $instance = $reflection->newInstanceArgs($dependencies);
            }

            if ($instance instanceof ContainerAwareInterface) {
                $instance->setContainer($container);
            }

            return $instance;
        } catch (ContainerException $exception) {
            throw $exception;
        } catch (Throwable $exception) {
            throw ContainerException::fromResolution($className, $exception);
        }
    }

    /**
     * Attempts to resolve any function parameters using provided container
     *
     * @param ReflectionFunctionAbstract $function
     * @param array $args
     * @return array
     * @throws Exception
     */
    public function resolveFunctionDependencies(ReflectionFunctionAbstract $function, ?array $args = [], $context = null ) : array
    {
        $args = $args ?? [];
        $container = $this->getContainer();
        $params = $function->getParameters();
        $dependencies = [];
        foreach ($params as $parameter) {
            $pname = $parameter->getName();
            $pclass = $parameter->getType() && !$parameter->getType()->isBuiltin()
                ? new ReflectionClass($parameter->getType()->getName())
                : null;
            $paramName = ($pclass) ? $pclass->getName() : $pname;
            if (array_key_exists($pname, $args)) { // first check if passed $args has the param name,
                $dependencies[] = $args[$pname];
            } else if ($container->has($paramName)) { // if now check if container has it
                try {
                    $dependencies[] = $container->get($paramName);
                } catch (Throwable $exception) {
                    throw ContainerException::fromParameter(
                        $this->describeFunction($function, $context),
                        $paramName,
                        $exception
                    );
                }
            } else if ($parameter->isOptional()) { // check if it is optional
                $dependencies[] = $parameter->getDefaultValue();
            } else { // unable to resolve required params,
                $typeName = $parameter->getType() ? $parameter->getType()->getName() : 'mixed';
                throw ContainerException::fromParameter(
                    $this->describeFunction($function, $context),
                    sprintf('%s (%s)', $paramName, $typeName)
                );
            }
        }

        return $dependencies;
    }

    private function describeResolvable($resolvable): string
    {
        if (is_string($resolvable)) {
            return $resolvable;
        }

        if (is_array($resolvable)) {
            [$classOrObject, $method] = $resolvable + [null, null];
            if (is_object($classOrObject)) {
                $classOrObject = get_class($classOrObject);
            }

            return sprintf('%s::%s', $classOrObject, $method);
        }

        if ($resolvable instanceof \Closure) {
            return '{closure}';
        }

        if (is_object($resolvable)) {
            return get_class($resolvable);
        }

        return (string) $resolvable;
    }

    private function describeFunction(ReflectionFunctionAbstract $function, $context = null): string
    {
        if ($function instanceof ReflectionMethod) {
            return $function->getDeclaringClass()->getName() . '::' . $function->getName();
        }

        if (is_string($context)) {
            return $context . '::' . $function->getName();
        }

        return $function->getName();
    }
}
