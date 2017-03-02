<?php
/**
 * Created by PhpStorm.
 * User: Alamin
 */

namespace Compose\Support;


use Compose\System\ConfigurationInterface;
use Zend\Stdlib\ArrayUtils;

/**
 * Class Configuration
 * @package Compose\Support
 */
class Configuration extends \ArrayObject implements ConfigurationInterface
{
    /**
     * default glob pattern (expressive skeleton)
     */
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

        $config = [];
        foreach (glob($glob, GLOB_BRACE) as $file) {
            $config = ArrayUtils::merge($config, include $file);
        }

        return $config;
    }
}