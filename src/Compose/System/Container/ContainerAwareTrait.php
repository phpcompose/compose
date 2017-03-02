<?php
/**
 * Created by PhpStorm.
 * User: alaminahmed
 * Date: 2016-10-23
 * Time: 7:42 PM
 */

namespace Compose\System\Container;


use Psr\Container\ContainerInterface;

/**
 * Trait ContainerAwareTrait
 *
 * Provides basic possible implementation of ContainerAwareTrait
 * @package Compose\Standard\Container
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