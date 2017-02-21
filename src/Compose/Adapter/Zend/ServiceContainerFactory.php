<?php
/**
 * Created by PhpStorm.
 * User: Alamin
 */
namespace Compose\Adapter\Zend;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\ServiceManager;
use Zend\ServiceManager\Config;
use Compose\Adapter\League\PlatesViewRenderer;
use Compose\Mvc\ViewRendererInterface;
use Compose\Standard\System\ConfigurationInterface;
use Compose\Support\Error\ViewResponseGenerator;
use Zend\Diactoros\Response;
use Zend\Stratigility\Middleware\ErrorHandler;

/**
 * Class ServiceContainerFactory
 * @package Compose\Adapter\Zend
 */
class ServiceContainerFactory
{
    const SERVICE_KEYS = ['dependencies', 'services'];

    /**
     * @param array $configs
     * @param array $keys
     * @return ContainerInterface
     */
    public function __invoke(array $configs = [], $keys = []) : ContainerInterface
    {
        $container = new ServiceManager();

        // first configure the service from the configs

        return $container;
    }

    protected function configureRequiredServices(ServiceManager $container)
    {
        // setup config object
        $config = $container->get('config') ?: [];
        if(!$config instanceof \ArrayObject) $config = new \ArrayObject($config);

        // autowire factory
        $container->addAbstractFactory(new ServiceAutowireFactory());

        // add configuration
        $container->setService(ConfigurationInterface::class, $config);

        // view renderer
        $container->setFactory(ViewRendererInterface::class, function(ContainerInterface $container) use ($config) {
            $templates = $config['templates'] ?: [];
            $renderer = new PlatesViewRenderer($templates['paths'] ?? []);
            return $renderer;
        });

        // error handler
        $container->setFactory(ErrorHandler::class, function(ContainerInterface $container) use ($config) {
            $templates = $config['templates'];
            $errors = $templates['errors'] ?? [];

            return new ErrorHandler(
                new Response(),
                new ViewResponseGenerator($container->get(ViewRendererInterface::class), $errors));
        });

        // not found handler
    }


    public function configure(ServiceManager $manager, array $config, array $keys) : void
    {
        if(!$keys) return;

        foreach($keys as $key) {
            if(!isset($config[$key])) continue;
            (new Config($config[$key]))->configureServiceManager($manager);
        }
    }
}