<?php
/**
 * Created by PhpStorm.
 * User: alaminahmed
 * Date: 2018-12-17
 * Time: 18:38
 */

namespace Compose\Container;
use Psr\Container\ContainerInterface;

trait ZendFactoryMapTrait
{
    public function __invoke(ContainerInterface $container, $id)
    {
        return self::create($container, $id);
    }
}