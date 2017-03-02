<?php
/**
 * Created by PhpStorm.
 * User: Alamin
 */

namespace Compose;


use Compose\Mvc\FrontController;
use Psr\Container\ContainerInterface;

/**
 * Class Application
 * @package Compose
 */
class Application extends FrontController
{
    protected
        /**
         * @var ContainerInterface
         */
        $container,

        /**
         * @var Dispatcher
         */
        $dispatcher;

}