<?php
/**
 * Created by PhpStorm.
 * User: alaminahmed
 * Date: 2018-03-28
 * Time: 1:06 PM
 */

namespace Compose\Support\Factory;


use Compose\Container\ServiceFactoryInterface;
use Compose\Http\Pipeline;
use Compose\Support\Configuration;
use Exception;
use Psr\Container\ContainerInterface;

class PipelineFactory implements ServiceFactoryInterface
{
    /**
     * @param ContainerInterface $container
     * @param string $name
     * @return mixed|void
     * @throws Exception
     */
    static public function create(ContainerInterface $container, string $name)
    {
        $config = $container->get(Configuration::class);
        $stack = $config['pipeline'] ?? [];

        $pipeline = new Pipeline();
        $pipeline->setContainer($container);

        $pipeline->pipeMany($stack['init'] ?? null);
        $pipeline->pipeMany($config['middleware'] ?? null);
        $pipeline->pipeMany($stack['ready'] ?? null);
        $pipeline->pipeMany($stack['routing'] ?? null);
        $pipeline->pipeMany($stack['final'] ?? null);
    }
}