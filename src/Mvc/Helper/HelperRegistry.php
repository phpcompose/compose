<?php
/**
 * Created by PhpStorm.
 * User: alaminahmed
 * Date: 2018-04-02
 * Time: 10:35 AM
 */

namespace Compose\Mvc\Helper;


use Compose\Container\ContainerAwareInterface;
use Compose\Container\ContainerAwareTrait;
use Compose\Container\ServiceResolver;
use Compose\Mvc\View;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class HelperRegistry
 * @package Compose\Mvc\Helper
 */
class HelperRegistry implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    public
        /**
         * @var View
         */
        $currentView,

        /**
         * @var ServerRequestInterface
         */
        $currentRequest;

    protected
        $resolver,
        $helpers = [],
        $instances = [];

    /**
     * HelperRegistry constructor.
     * @param ServiceResolver $resolver
     */
    public function __construct(ServiceResolver $resolver)
    {
        $this->resolver = $resolver;
    }

    public function __invoke()
    {
        return $this;
    }

    /**
     * @param string $name
     * @param $helper
     * @throws \Exception
     */
    public function register(string $name, $helper)
    {
        if(method_exists($this, $name)) {
            throw new \Exception("Cannot register helper with this name: " . $name);
        }

        if(isset($this->helpers[$name])) {
            throw new \Exception("Helper is already registered with that name: " . $name);
        }

        $this->helpers[$name] = $helper;
    }

    /**
     * Apply methods of give class (static) or object to the helper registry
     * @param $objectOrClass
     * @param array|null $helpers
     * @throws \ReflectionException
     */
    public function extend($objectOrClass, array $methods = null)
    {
        $reflector = new \ReflectionClass($objectOrClass);

        if(!$methods) {
            $methods = $reflector->getMethods(\ReflectionMethod::IS_PUBLIC);
            foreach ($methods as $method) {
                $name = $method->getName();
                if(strpos($name, '__') === 0) continue; // ignore magic methods

                $this->register($name, $objectOrClass);
            }
        } else {
            foreach($methods as $name) {
                $this->register($name, $objectOrClass);
            }
        }
    }

    /**
     * @param string $name
     * @return mixed|null|object
     * @throws \ReflectionException
     */
    public function get(string $name)
    {
        $helper = $this->helpers[$name] ?? null;
        if(!$helper) {
            throw new \Exception("Helper is not registered: " . $name);
        }

        if(is_string($helper)) {
            $instance = $this->instances[$helper] ?? null;
            if(!$instance) {
                $instance = $this->resolver->resolve($helper);
                if(property_exists($instance, 'registry')) {
                    $instance->registry = $this;
                }

                $this->instances[$helper] = $instance;
            }

            $helper = $instance;
        }

        return $helper;
    }

    /**
     * @param array $names
     * @return array
     * @throws \ReflectionException
     */
    public function getMany(array $names)
    {
        $helpers = [];
        foreach($names as $name) {
            $helpers[$name] = $this->get($name);
        }

        return $helpers;
    }


    /**
     * @param $name
     * @param $arguments
     * @return mixed
     * @throws \Exception
     */
    public function __call($name, $arguments)
    {
        $helper = $this->get($name);

        if(is_callable($helper)) {
            return call_user_func_array($helper, $arguments);
        } else if(is_object($helper)) {
            return call_user_func_array([$helper, $name], $arguments);
        } else {
            throw new \Exception('Unable to execute helper: ' . $name);
        }
    }

    /**
     * @param $name
     * @return mixed|null|object
     * @throws \ReflectionException
     */
    public function __get($name)
    {
        return $this->get($name);
    }
}