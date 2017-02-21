<?php
/**
 * Created by PhpStorm.
 * User: alaminahmed
 */
namespace Compose\Standard\System;

/**
 * Interface Invocable
 * @package Compose\Standard\System
 */
interface Invocable
{
    /**
     * @param array ...$args
     * @return mixed
     */
    public function __invoke(...$args);
}