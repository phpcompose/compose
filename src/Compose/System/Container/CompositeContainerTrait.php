<?php
/**
 * Created by PhpStorm.
 * User: alaminahmed
 * Date: 2016-10-23
 * Time: 8:20 PM
 */

namespace Compose\System\Container;


use Interop\Container\ContainerInterface;
use Interop\Container\Exception\NotFoundException;

/**
 * Trait CompositeContainerTrait
 *
 * Provides simple implementation of CompositeContainerInterface
 * There has been various approaches to implementing a composite container.  Concept has been discussed at linked provided below.
 *
 * @link https://github.com/container-interop/container-interop/blob/master/docs/Delegate-lookup-meta.md
 * @package Compose\Standard\Container
 */
trait CompositeContainerTrait
{
    protected
        /**
         * @var ContainerInterface[]
         */
        $containers = [],

        /**
         * Holds service mapped to container index, if available
         *
         * @var array
         */
        $map = [];

    /**
     * @inheritdoc
     * @param ContainerInterface $container
     */
    public function addDelegate(ContainerInterface $container)
    {
        if($container instanceof DelegateContainerInterface) {
            $container->setContainer($this);
        }

        $this->containers[] = $container;
    }


    /**
     * Loops through all delegate containers to check if given service $name is available
     *
     * @param $name
     * @return bool
     */
    public function has($name)
    {
        // if the name already has been resolved, we can simply return true
        if(array_key_exists($name, $this->map)) {
            return true;
        }

        // attempt to loop through to check if service
        for ($i = 0; $i < count($this->containers); $i++) {
            /** @var ContainerInterface $container */
            $container = $this->containers[$i];
            if($container->has($name)) {
                $this->map[$name] = $i;
                return true;
            }
        }

        return false;
    }


    /**
     * @inheritdoc
     * @param $name
     * @return mixed
     */
    public function get($name)
    {
        if(!$this->has($name)) {
            // unable to resolve given service $name
            throw new class("Service {$name} not found.") extends \Exception implements NotFoundException {};
        }

        $index = $this->map[$name];

        /** @var ContainerInterface $container */
        $container = $this->containers[$index];
        return $container->get($name);
    }
}