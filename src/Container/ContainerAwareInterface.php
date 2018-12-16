<?php
namespace Compose\Container;

use Psr\Container\ContainerInterface;


/**
 * Interface ContainerAwareInterface
 *
 * This interface requests Service Store to be injected in the class
 * This will only be possible if Store is instantiating the class.  Therefore, ServiceInjectInterface is required
 */
interface ContainerAwareInterface extends ResolvableInterface
{
    /**
     * Sets/Injects the Service Store
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
}