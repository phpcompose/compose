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
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @inheritdoc
     * @param ContainerInterface $container
     */
    public function setContainer(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @inheritdoc
     * @return ContainerInterface
     */
    public function getContainer() : ContainerInterface
    {
        return $this->container;
    }
}