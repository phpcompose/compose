<?php
/**
 * Created by PhpStorm.
 * User: Alamin Ahmed
 * Date: 2016-04-06
 * Time: 9:24 AM
 */

namespace Compose\Common;

use Interop\Container\ContainerInterface;

/**
 * Interface ServiceContainerAwareInterface
 *
 * This interface will only work when ServiceFactory is creating the service.
 * This usually happen when Service/Middleware/Action does not have explicit factory defined in the config
 *
 * @see ServiceFactory documentation
 * @package Ats\Support
 */
interface ServiceContainerAwareInterface extends ServiceAwareInterface
{
    /**
     * Set the current application service container
     *
     * This will be called by ServiceFactory when instantiating using ContainerInterface
     * @param ContainerInterface $container
     * @return void
     */
    public function setServiceContainer(ContainerInterface $container);

    /**
     * @return ContainerInterface
     */
    public function getServiceContainer() : ContainerInterface;
}

