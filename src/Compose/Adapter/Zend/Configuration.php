<?php
/**
 * Created by PhpStorm.
 * User: Alamin
 */

namespace Compose\Adapter\Zend;


use Compose\System\ConfigurationInterface;
use Zend\Config\Config;
use Zend\Config\Factory;

/**
 * Class Configuration
 * @package Compose\Adapter\Zend
 */
class Configuration extends Config implements ConfigurationInterface
{
    const AUTOLOAD_GLOB = '{{,*.}global,{,*.}local}.php';

    /**
     *
     * @param string $dir
     * @param string|null $globPattern
     * @return array
     */
    static public function autoload(string $dir, string $globPattern = null) : array
    {
        $glob = rtrim($dir, '/') . '/' . ($globPattern ?: self::AUTOLOAD_GLOB);

        return Factory::fromFiles(glob($glob, GLOB_BRACE));
    }
}