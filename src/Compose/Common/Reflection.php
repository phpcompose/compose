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
    protected function validateParameters(\ReflectionFunctionAbstract $method, array $args = null)
    {
        // now we will validate the function with given $args
        $argsCount = ($args === null) ? 0 : count($args);
        if ($argsCount < $method->getNumberOfRequiredParameters()) {
            throw new \InvalidArgumentException("Invalid Param count. (less than required)");
        }

        if ($argsCount > $method->getNumberOfParameters()) {
            throw new \InvalidArgumentException("Invalid Param count. (more than allowed)");
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