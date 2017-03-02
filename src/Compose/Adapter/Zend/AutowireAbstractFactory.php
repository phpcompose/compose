<?php
/**
 * Created by PhpStorm.
 * User: Alamin
 */

namespace Compose\Adapter\Zend;


use Compose\System\Container\ServiceContainer;
use Psr\Container\ContainerInterface;
use Zend\ServiceManager\Factory\AbstractFactoryInterface;
use Zend\ServiceManager\ServiceManager;


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
        /**
         * @var ServiceContainer
         */
        $resolver;


    /**
     * AutowireAbstractFactory constructor.
     * @param ServiceManager $serviceManager
     */
    public function __construct(ServiceManager $serviceManager)
    {
        $this->resolver = new ServiceContainer();
        $this->resolver->setContainer($serviceManager);
    }

    /**
     *
     * @param ContainerInterface $container
     * @param string $name
     * @return bool
     */
    public function canCreate(ContainerInterface $container, $name)
    {
        return $this->resolver->has($name);
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
        return $this->resolver->get($requestedName);
    }
}
