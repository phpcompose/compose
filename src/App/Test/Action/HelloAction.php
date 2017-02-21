<?php
/**
 * Created by PhpStorm.
 * User: Alamin
 * Date: 2016-10-16
 * Time: 9:15 PM
 */
namespace App\Test\Action;


use Compose\Adapter\League\PlatesViewRenderer;
use Compose\Mvc\Action;
use Compose\Mvc\ViewRendererInterface;
use League\Plates\Engine;
use League\Plates\Template\Template;
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
        $renderer = $this->container->get(ViewRendererInterface::class);
        $script = 'templates/test/hello';

        return $this->html($renderer->render($script));
    }
}