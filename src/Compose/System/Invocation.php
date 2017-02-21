<?php
/**
 * Created by PhpStorm.
 * User: alaminahmed
 * Date: 2016-10-26
 * Time: 10:09 PM
 */

namespace Compose\System;



use Compose\Standard\System\Invocable;

/**
 * Class Invocation
 * @package Compose\System
 */
class Invocation implements Invocable
{
    protected
        /**
         * @var null|\ReflectionFunctionAbstract
         */
        $reflection = null,

        /**
         * @var string
         */
        $name,

        /**
         * @var object
         */
        $target,

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
    public function __construct($target, string $name, array $parameters = null)
    {
        $this->target = $target;
        $this->name = $name;
        if($parameters) $this->parameters = $parameters;
    }

    /**
     * Get the method/function name
     * @return string
     */
    public function getName() : string
    {
        return $this->name;
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
     * @return null|mixed
     */
    public function getTarget()
    {
        return $this->target;
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
        if($this->target == null) {
            throw new \InvalidArgumentException("Target for Invocation is not specified.");
        }

        $reflection = $this->getReflection();
        $params = $params ?: $this->getParameters();

        $this->verify($reflection, $params);

        return $reflection->invokeArgs($this->target, $params);
    }

    /**
     * @return \ReflectionMethod
     */
    public function getReflection() : \ReflectionMethod
    {
        if(!$this->reflection) {
            $this->reflection = new \ReflectionMethod($this->target, $this->name);
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