<?php
/**
 * Created by PhpStorm.
 * User: Alamin
 */

namespace Compose\System;

/**
 * Interface ConfigurationInterface
 * @package Compose\System
 */
interface ConfigurationInterface extends \ArrayAccess, \Countable, \Iterator
{
    public function get($name, $default = null);
//    public function getByPath(string $path, $default = null, string $separator = '.');
}