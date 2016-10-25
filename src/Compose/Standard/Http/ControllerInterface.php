<?php
/**
 * Created by PhpStorm.
 * User: alaminahmed
 * Date: 2016-10-24
 * Time: 9:03 PM
 */

namespace Compose\Standard\Http;


use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

interface ControllerInterface
{
    public function forward(ServerRequestInterface $request) : ResponseInterface;
}