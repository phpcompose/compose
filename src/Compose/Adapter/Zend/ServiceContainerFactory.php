<?php
/**
 * Created by PhpStorm.
 * User: Alamin
 */
namespace Compose\Adapter\Zend;

use Compose\System\ConfigurationInterface;
use Zend\ServiceManager\ServiceManager;
use Zend\ServiceManager\Config;

/**
 * Class ServiceContainerFactory
 * @package Compose\Adapter\Zend
 */
class ServiceContainerFactory
{
    const CONFIG_KEY = 'dependencies';

    /**
     * @param array $dependencies
     * @return ServiceManager
     */
    public function __invoke(array $dependencies = []) : ServiceManager
    {
        $manager = new ServiceManager();

        // first configure the service from the configs
        if($dependencies) {
            (new Config($dependencies))->configureServiceManager($manager);
        }

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
        $container = $factory($config[self::CONFIG_KEY] ?? []);
        $container->setService(ConfigurationInterface::class, new Configuration($config));

        return $container;
    }
}