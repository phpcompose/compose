<?php
/**
 * Created by PhpStorm.
 * User: Alamin
 */
namespace Compose\Adapter\Zend;

use Zend\ServiceManager\ServiceManager;
use Zend\ServiceManager\Config;

/**
 * Class ServiceContainerFactory
 * @package Compose\Adapter\Zend
 */
class ServiceContainerFactory
{
    /**
     * @param array $config
     * @return ServiceManager
     */
    public function __invoke(array $config = []) : ServiceManager
    {
        $manager = new ServiceManager();

        // first configure the service from the configs
        (new Config($config))->configureServiceManager($manager);

        // add the abstract factory
        $manager->addAbstractFactory(new AutowireAbstractFactory($manager));

        return $manager;
    }

    /**
     * @param array $config
     * @return ServiceManager
     */
    static public function createFromConfig(array $config) : ServiceManager
    {
        $factory = new self();
        return $factory($config);
    }
}