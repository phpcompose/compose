<?php
/**
 * Created by PhpStorm.
 * User: Alamin
 */

namespace Compose\System\Container;


use Interop\Container\ContainerInterface;

interface DelegateContainerInterface
{
    public function setContainer(ContainerInterface $container);
}