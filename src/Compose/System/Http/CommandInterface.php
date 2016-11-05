<?php
/**
 * Created by PhpStorm.
 * User: alaminahmed
 * Date: 2016-10-24
 * Time: 8:10 PM
 */

namespace Compose\System\Http;


use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

interface CommandInterface
{
    public function execute(ServerRequestInterface $request) : ResponseInterface;
}