<?php
/**
 * Created by PhpStorm.
 * User: alaminahmed
 * Date: 2018-01-09
 * Time: 12:53 PM
 */

namespace Compose\Support\Factory;


use Compose\Container\ServiceFactoryInterface;
use Compose\Mvc\Helper\HelperRegistry;
use Compose\Mvc\ViewRenderer;
use Compose\Support\Configuration;
use Psr\Container\ContainerInterface;

class ViewRendererFactory implements ServiceFactoryInterface
{
    /**
     * @param ContainerInterface $container
     * @return ViewRenderer
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    static public function create(ContainerInterface $container, string $name)
    {
        $configuration = $container->get(Configuration::class);
        $renderer = new ViewRenderer($configuration['templates'] ?? [], $container->get(HelperRegistry::class));

        return $renderer;
    }


    public function __invoke(ContainerInterface $container, $id)
    {
        return self::create($container, $id);
    }
}