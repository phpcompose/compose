<?php
/**
 * Created by PhpStorm.
 * User: Alamin
 */

namespace Compose\System\Container;

/**
 * Class CompositeContainer
 * @package Compose\System\Container
 */
class CompositeContainer implements CompositeContainerInterface
{
    use CompositeContainerTrait;

    /**
     * CompositeContainer constructor.
     * @param array $containers
     */
    public function __construct(array $containers = [])
    {
        if($containers) $this->setDelegates($containers);
    }


    /**
     * Sets an array of containers
     *
     * This will also remove any existing containers, if any
     * @param array $containers
     */
    public function setDelegates(array $containers) : void
    {
        $this->containers = []; // removes existing

        foreach($containers as $container) {
            $this->addDelegate($container);
        }
    }

    /**
     * Get all existing delegates
     *
     * @return array
     */
    public function getDelegates() : array
    {
        return $this->containers;
    }
}