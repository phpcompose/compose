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
    public function __construct(string $name, array $parameters = null, $target = null)
    {
        $this->name = $name;
        if($parameters) $this->parameters = $parameters;
        if($target) $this->target = $target;
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
     * @param mixed $arguments
     */
    public function setParameters($arguments)
    {
        $this->parameters = $arguments;
    }

    /**
     * @param null|mixed $object
     */
    public function setTarget($object)
    {
        $this->target = $object;
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
     * Will validate method signature with current params
     * @throws \ReflectionException
     * @throws \InvalidArgumentException
     * @return mixed
     */
    public function __invoke()
    {
        if($this->target == null) {
            throw new \InvalidArgumentException("Target for Invocation is not specified.");
        }

        $reflection = new \ReflectionMethod($this->target, $this->name);
        $params = $this->getParameters();

        $this->verify($reflection, $params);

        return $reflection->invokeArgs($this->target, $params);
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
            throw new \InvalidArgumentException("Invalid Param count. (Params ({$argsCount}) are less then method anticipates ({$requiredParamsCount}))");
        }

        if ($argsCount > $paramsCount) {
            throw new \InvalidArgumentException("Invalid Param count. (Params ({$argsCount}) are more than method anticipates ({$requiredParamsCount}))");
        }
    }
}