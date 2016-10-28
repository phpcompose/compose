<?php
/**
 * Created by PhpStorm.
 * User: alaminahmed
 * Date: 2016-10-26
 * Time: 6:21 PM
 */

namespace Compose\Express;


use Compose\Standard\Http\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Zend\Expressive\Router\RouterInterface;

class Dispatcher implements MiddlewareInterface
{
    public function __construct()
    {

    }

    public function __invoke(
        ServerRequestInterface $request,
        ResponseInterface $response,
        callable $next = null
    ) : ResponseInterface
    {


    }
}