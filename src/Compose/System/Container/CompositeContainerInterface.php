<?php
/**
 * Created by PhpStorm.
 * User: alaminahmed
 * Date: 2016-10-23
 * Time: 7:06 PM
 */

namespace Compose\System\Container;


use Interop\Container\ContainerInterface;

/**
 * Interface CompositeContainerInterface
 *
 * Composite Container based on discussion on Container-interop
 * @link https://github.com/container-interop/container-interop/blob/master/docs/Delegate-lookup-meta.md
 *
 * Composite Container actually don't provide any storage facility.
 * Instead it relies on Delegates to store/resolve services
 *
 * @package Compose\Standard\Container
 */
interface CompositeContainerInterface extends ContainerInterface
{
    /**
     * @param ContainerInterface $container
     *
     * Adds delegate container
     * If the delegate container needs to have access to this compositeContainer,
     * it should also implement ContainerAwareInterface
     * @return mixed
     */
    public function addDelegate(ContainerInterface $container);
}