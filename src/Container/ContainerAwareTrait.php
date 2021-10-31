<?php

namespace Compose\Container;


use Psr\Container\ContainerInterface;

/**
 * Trait ContainerAwareTrait
 *
 * Provides basic possible implementation of ContainerAwareTrait
 */
trait ContainerAwareTrait
{
    protected ContainerInterface $container;

    /**
     * @inheritdoc
     * @param ContainerInterface $container
     */
    public function setContainer(ContainerInterface $container) : void
    {
        $this->container = $container;
    }

    /**
     * @return ContainerInterface
     */
    public function getContainer() : ContainerInterface
    {
        return $this->container;
    }
}