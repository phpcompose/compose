<?php
/**
 * Created by PhpStorm.
 * User: alaminahmed
 * Date: 2016-10-23
 * Time: 10:25 AM
 */

namespace Compose\Standard\Container;


/**
 * Class ServiceInterface
 *
 * This is a typehint interface for letting the Container know that this class is a Service for the Container
 * This interface does not provide any methods
 *
 * The purpose of this Interface is for Container to recognize the class so that it can try to instantiate or delegate
 *
 * THIS WILL ONLY RESOLVE CONSTRUCTOR DEPENDENCIES
 * @package Compose\Standard\Cointainer
 */
interface ServiceInterface {}