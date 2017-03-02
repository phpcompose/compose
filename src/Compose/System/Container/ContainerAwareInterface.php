<?php
/**
 * Created by PhpStorm.
 * User: alaminahmed
 * Date: 2016-10-23
 * Time: 10:33 AM
 */

namespace Compose\System\Container;

use Psr\Container\ContainerInterface;


/**
 * Interface ContainerAwareInterface
 *
 * This interface requests Service Container to be injected in the class
 * This will only be possible if Container is instantiating the class.  Therefore, ServiceInjectInterface is required
 * @package Compose\Standard
 */
interface ContainerAwareInterface extends ServiceInterface
{
    /**
     * Sets/Injects the Service Container
     *
     * Classes implementing this class should use this method to store the container in instance member
     * Class should also extract any required dependencies in this method and store them locally.
     * This will make dependencies of the class clear
     *
     * Sub classes should ALWAYS call parent::setContainer if overriding this method
     * @param ContainerInterface $container
     * @return mixed
     */
    public function setContainer(ContainerInterface $container);

    /**
     * Returns the Container class
     *
     * @return ContainerInterface
     */
    public function getContainer() : ContainerInterface;
}