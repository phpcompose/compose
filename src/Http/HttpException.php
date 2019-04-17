<?php
namespace Compose\Http;


use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class HttpException extends \Exception
{
    public
        /**
         * @var ServerRequestInterface
         */
        $request,

        /**
         * @var ResponseInterface
         */
        $response;

}