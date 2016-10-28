<?php
/**
 * Created by PhpStorm.
 * User: alaminahmed
 * Date: 2016-10-26
 * Time: 5:30 PM
 */

namespace Compose\Standard\Http;


interface RouteInterface
{
    public function getPath() : string;
    public function setPath(string $path);
    public function getMethod() : string;
    public function setMethod(string $method);
    public function setParams(arary $param);
    public function getParams(): array;
}


interface RouteResultInterface
{
    public function getParams() : array;
    public function setParams(array $array);
    public function setMethod(string $method);
    public function getMethod() : string;
}