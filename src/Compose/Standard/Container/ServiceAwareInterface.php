<?php
/**
 * Created by PhpStorm.
 * User: alaminahmed
 * Date: 2016-10-23
 * Time: 1:19 PM
 */

namespace Compose\Standard\Container;


/**
 * Interface ServiceAwareInterface
 *
 * Automatic class instantiation is not always a good practice and may have unwanted side effect.
 * This interface makes it clear and explicit if a Service wants to be auto-wired with dependencies resolved
 * Therefore, if service container does not contain the service requested,
 * It should only attempt to auto-wire if requested class implements this method

 * @package Compose\Standard\Container
 */
interface ServiceAwareInterface extends ServiceInterface
{}