<?php
/**
 * Created by PhpStorm.
 * User: Alamin
 */

namespace Compose\Support;


use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\AbstractFactoryInterface;


/**
 * Class ServiceFactory
 *
 * Zend Expressive supports and highly recommends to provide explicit factory for all services/middlewares
 * However if your action middleware simply needs some dependencies to be injected to the constructor,
 * This factory will attempt to auto satisfy those dependencies.
 * @package Ats\Support
 */
class AutowireAbstractFactory implements AbstractFactoryInterface
{
    protected
        $resolver,


        $services = [];


    public function __construct()
    {
    }



    /**
     *
     * @param ContainerInterface $container
     * @param string $name
     * @return bool
     */
    public function canCreate(ContainerInterface $container, $name)
    {
        return $this->has($name);
    }

    /**
     * Uses reflection to perform simple iteration over constructor dependencies.
     *
     * @param ContainerInterface $container
     * @param string $requestedName
     * @param array|null $options
     * @return object
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {

    }
}
