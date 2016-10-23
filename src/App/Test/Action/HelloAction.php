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

    public function __construct(HelperPluginManager $helpers)
    {
    }

    public function execute(ServerRequestInterface $request) : ResponseInterface
    {
        return $this->view('app::test\hello', [
            'name' => 'Alamin Ahmed'
        ]);
    }
}