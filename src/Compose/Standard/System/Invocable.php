<?php

namespace Compose\Standard\System;

/**
 * Created by PhpStorm.
 * User: alaminahmed
 * Date: 2016-10-27
 * Time: 8:34 PM
 */
interface Invocable
{
    /**
     * @param array ...$args
     * @return mixed
     */
    public function __invoke(...$args);
}