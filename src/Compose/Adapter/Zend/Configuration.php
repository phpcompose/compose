<?php
/**
 * Created by PhpStorm.
 * User: Alamin
 */

namespace Compose\Adapter\Zend;


use Compose\System\ConfigurationInterface;
use Zend\Config\Config;

class Configuration extends Config implements ConfigurationInterface
{
    public function __construct(array $array, $allowModifications = false)
    {
        parent::__construct($array, $allowModifications);
    }
}