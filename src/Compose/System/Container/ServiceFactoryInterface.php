<?php
/**
 * Created by PhpStorm.
 * User: Alamin
 */

namespace Compose\System\Container;


use Psr\Container\ContainerInterface;

interface ServiceFactoryInterface
{
    static public function create(ContainerInterface $container);
}