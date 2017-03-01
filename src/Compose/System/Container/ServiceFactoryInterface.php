<?php
/**
 * Created by PhpStorm.
 * User: Alamin
 */

namespace Compose\System\Container;


use Interop\Container\ContainerInterface;

interface ServiceFactoryInterface
{
    static public function create(ContainerInterface $container);
}