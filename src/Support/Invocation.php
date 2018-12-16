<?php
/**
 * Created by PhpStorm.
 * User: alaminahmed
 * Date: 2016-10-26
 * Time: 10:09 PM
 */

namespace Compose\Support;



/**
 * Class Invocation
 * @package Compose\System
 */
class Invocation
{
    protected
        /**
         * @var null|\ReflectionFunctionAbstract
         */
        $reflection = null,

        /**
         * @var callable
         */
        $callable,

        /**
         * @var array
         */
        $parameters = [];

    /**
     * Invocation constructor.
     * @param string $name
     * @param array|null $parameters
     * @param null $target
     */
    public function __construct(callable $callable, array $parameters = null)
    {
        $this->callable = $callable;
        if($parameters) $this->parameters = $parameters;
    }

    /**
     * @return callable
     */
    public function getCallable() : callable
    {
        return $this->callable;
    }

    /**
     * @return array
     */
    public function getParameters() : array
    {
        return $this->parameters;
    }

    /**
     * Sets params for the invocation method
     *
     * @param mixed $arguments
     */
    public function setParameters(array $arguments)
    {
        $this->parameters = $arguments;
    }

    /**
     * Attempt to invoke the invocation
     *
     * Will use reflection to validate and invoke the callable
     * Will validate method signature with params
     *
     * If params is passed, it will be used, else will attempt to use from getParameters()
     * @param $params
     * @throws \ReflectionException
     * @throws \InvalidArgumentException
     * @return mixed
     */
    public function __invoke(...$params)
    {
        $reflection = $this->getReflection();
        $params = $params ?: $this->getParameters();

        $this->verify($reflection, $params);

        return $reflection->invokeArgs($params);
    }


    /**
     * Attempt to get reflection for given callable
     *
     * @param callable $callable
     * @return \ReflectionFunction
     * @throws \ReflectionException
     */
    static public function reflectCallable(callable $callable) : \ReflectionFunction
    {
        if(!$callable instanceof \Closure) {
            $closure = \Closure::fromCallable($callable);
        } else {
            $closure = $callable;
        }

        return new \ReflectionFunction($closure);
    }

    /**
     * @return \ReflectionFunctionAbstract
     */
    public function getReflection() : \ReflectionFunction
    {
        if(!$this->reflection) {
            $this->reflection = self::reflectCallable($this->callable);
        }

        return $this->reflection;
    }

    /**
     * @param \ReflectionFunctionAbstract $method
     * @param array $args
     */
    protected function verify(\ReflectionFunctionAbstract $method, array $args = [])
    {
        // now we will validate the function with given $args
        $argsCount = ($args === null) ? 0 : count($args);
        $paramsCount = $method->getNumberOfParameters();
        $requiredParamsCount = 0;

        if(!$method->isVariadic()) { // for non-variadic methods, we can do traditional checks for params
            $requiredParamsCount = $method->getNumberOfRequiredParameters();
        } else {
            foreach ($method->getParameters() as $parameter) {
                if($parameter->isVariadic()) {
                    // if we find variadic params
                    // we need to allow all other input arguments
                    $paramsCount = $argsCount;
                    break;
                }

                if($parameter->isOptional()) {
                    break;
                }

                $requiredParamsCount++;
            }
        }

        if ($argsCount < $requiredParamsCount) {
            throw new \InvalidArgumentException("{$method->getName()}: Invalid Param count. (Params ({$argsCount}) are less then method anticipates ({$requiredParamsCount}))");
        }

        if ($argsCount > $paramsCount) {
            throw new \InvalidArgumentException("{$method->getName()}: Invalid Param count. (Params ({$argsCount}) are more than method anticipates ({$requiredParamsCount}))");
        }
    }

    /**
     * @param int $index
     * @return null|string
     */
    public function getArgumentTypeAtIndex(int $index) : ?string
    {
        $reflection = $this->getReflection();
        $param = isset($reflection->getParameters()[$index]) ? $reflection->getParameters()[$index] : null;
        if(!$param) return null;

        return (string) $param->getType();
    }
}