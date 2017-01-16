<?php
/**
 * Created by PhpStorm.
 * User: Alamin
 * Date: 2016-10-16
 * Time: 9:15 PM
 */
namespace App\Test\Action;


use Compose\Express\Action;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class HelloAction extends Action
{
    /**
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function onProcessGet(ServerRequestInterface $request) : ResponseInterface
    {
        return $this->html('<h1>Hell</h1>');
    }
}