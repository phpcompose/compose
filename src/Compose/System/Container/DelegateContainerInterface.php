<?php
/**
 * Created by PhpStorm.
 * User: Alamin
 */

namespace Compose\System\Container;


use Psr\Container\ContainerInterface;

/**
 * Interface DelegateContainerInterface
 *
 * Delegate for the Composite Container
 * @package Compose\System\Container
 */
interface DelegateContainerInterface
{
    /**
     * @param ContainerInterface $container
     * @return mixed
     */
    public function setContainer(ContainerInterface $container);
}