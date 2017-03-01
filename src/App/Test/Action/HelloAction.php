<?php
/**
 * Created by PhpStorm.
 * User: Alamin
 * Date: 2016-10-16
 * Time: 9:15 PM
 */
namespace App\Test\Action;


use Compose\Mvc\Action;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class HelloAction extends Action
{
    /**
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function onIndex(ServerRequestInterface $request) : ResponseInterface
    {
        return $this->text('app::test/hello');
    }
}