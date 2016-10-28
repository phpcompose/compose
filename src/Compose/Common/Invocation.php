<?php
/**
 * Created by PhpStorm.
 * User: alaminahmed
 * Date: 2016-10-26
 * Time: 10:09 PM
 */

namespace Compose\Common;



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
        $parameters;

    /**
     * Invocation constructor.
     * @param callable $callable
     * @param array $parameters
     */
    public function __construct(callable $callable, array $parameters = [])
    {
        $this->callable = $callable;
        if($parameters) $this->parameters = $parameters;
    }

    /**
     * @return array
     */
    public function getParameters() : array
    {
        return $this->parameters;
    }

    /**
     * @param mixed $arguments
     */
    public function setParameters($arguments)
    {
        $this->parameters = $arguments;
    }

    /**
     * Attempt to invoke the invocation
     *
     * Will use reflection to validate and invoke the callable
     * Will validate method signature with current params
     * @throws \ReflectionException
     * @throws \InvalidArgumentException
     * @return mixed
     */
    public function __invoke()
    {
        $reflection = $this->reflect();
        $params = $this->getParameters();

        $this->verify($reflection, $params);

        return $reflection->invokeArgs($params);
    }

    /**
     * @return \ReflectionFunctionAbstract
     * @throws \ReflectionException
     */
    public function reflect() : \ReflectionFunctionAbstract
    {
        if(!$this->reflection) {
            $callable = $this->callable;
            $reflection = null;
            if (is_string($callable) || $callable instanceof \Closure) {
                $reflection = new \ReflectionFunction($callable);
            } elseif (is_array($callable) && count($callable) == 2) { // standard php [class, method] syntax
                list($class, $method) = $callable;
                $reflection = new \ReflectionMethod($class, $method);
            } else {
                throw new \ReflectionException("Unable to reflect Invocation.");
            }
            $this->reflection = $reflection;
        }

        return $this->reflection;
    }

    /**
     * @throws \InvalidArgumentException
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
            throw new \InvalidArgumentException("Invalid Param count. (Params ({$argsCount}) are less then method anticipates ({$requiredParamsCount}))");
        }

        if ($argsCount > $paramsCount) {
            throw new \InvalidArgumentException("Invalid Param count. (Params ({$argsCount}) are more than method anticipates ({$requiredParamsCount}))");
        }
    }
}