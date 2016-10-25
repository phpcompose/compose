<?php
/**
 * Created by PhpStorm.
 * User: alaminahmed
 * Date: 2016-10-24
 * Time: 8:10 PM
 */

namespace Compose\Standard\Http;


use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

interface CommandInterface
{
    public function execute(RequestInterface $request) : ResponseInterface;
    public function __invoke(RequestInterface $request, ResponseInterface $response) : ResponseInterface;
}