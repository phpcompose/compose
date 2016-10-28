<?php
/**
 * Created by PhpStorm.
 * User: Alamin
 * Date: 2016-10-18
 * Time: 9:30 PM
 */

namespace Compose\Common;


class Reflection
{

    /**
     * Attempt to get reflection for given callable, method
     *
     * @todo NOT COMPLETE.
     * @note temporary solution until php 7.1 Closure::fromCallable()
     * @param callable $callable
     * @return \ReflectionFunctionAbstract
     */
    protected function reflectCallable(callable $callable)
    {
        $reflection = null;
        if (is_string($callable) || $callable instanceof \Closure) {
            $reflection = new \ReflectionFunction($callable);
        } elseif(is_array($callable) && count($callable) == 2) { // standard php [class, method] syntax
            list($class, $method) = $callable;
            $reflection = new \ReflectionMethod($class, $method);
        }

        return $reflection;
    }

    /**
     * @param \ReflectionFunctionAbstract $method
     * @param array|null $args
     * @throws \InvalidArgumentException
     */
    public function validateParameters(\ReflectionFunctionAbstract $method, array $args = null)
    {

        // now we will validate the function with given $args
        $argsCount = ($args === null) ? 0 : count($args);
        $paramsCount = $method->getNumberOfParameters();
        $requiredParamsCount = 0;
        $optionalParamsCount = 0;

        if(!$method->isVariadic()) { // for non-variadic methods, we can do traditional checks for params
            $requiredParamsCount = $method->getNumberOfRequiredParameters();
        } else {
            foreach ($method->getParameters() as $parameter) {
                if($parameter->isVariadic()) {
                    // if we find variadic params
                    // we need to allow all other input arguments
                    // therefore,
                    // 1) If $argsCount is more then $paramCount, we need to allow that
                    $paramsCount = $argsCount;
                    break;
                }

                if($parameter->isOptional()) {
                    break;
                }

                $requiredParamsCount++;
            }
        }

        $optionalParamsCount = ($paramsCount - $requiredParamsCount);

        if ($argsCount < $requiredParamsCount) {
            throw new \InvalidArgumentException("Invalid Param count. (Params ({$argsCount}) are less then method anticipates ({$requiredParamsCount}))");
        }

        if ($argsCount > $paramsCount) {
            throw new \InvalidArgumentException("Invalid Param count. (Params ({$argsCount}) are more than method anticipates ({$requiredParamsCount}))");
        }
    }

    /**
     * Check if given object/class implements interface
     *
     * @param $objOrClass
     * @param $interface
     * @return bool
     */
    public function implements($objOrClass, $interface)
    {
        if(is_object($objOrClass)) {
            return $objOrClass instanceof $interface;
        } else {
            if(class_exists($objOrClass)) {
                return in_array($interface, class_implements($objOrClass));
            }
        }

        return false;
    }
}