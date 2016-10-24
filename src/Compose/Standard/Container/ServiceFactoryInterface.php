<?php
/**
 * Created by PhpStorm.
 * User: alaminahmed
 * Date: 2016-10-23
 * Time: 11:34 AM
 */

namespace Compose\Standard\Container;


use Interop\Container\ContainerInterface;

/**
 * Interface ServiceFactoryInterface
 *
 * This interface provides factory method for creating the implementing class,
 * This way Container will NOT invoke constructor for instantiating,
 * instead will call the create method for creating the service
 *
 * @package Compose\Standard\Container
 */
interface ServiceFactoryInterface extends ServiceInterface
{
    /**
     * Static factory method for creating service for provided container
     *
     * @param ContainerInterface $container
     * @return self
     */
    public static function create(ContainerInterface $container) : self;
}