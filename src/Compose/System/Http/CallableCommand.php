<?php
/**
 * Created by PhpStorm.
 * User: Alamin
 */

namespace Compose\System\Http;


use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class CallableCommand
 * @package Compose\System\Http
 */
class CallableCommand implements CommandInterface
{
    protected
        /**
         * @var callable
         */
        $callable;


    /**
     * CallableCommand constructor.
     * @param callable $callable
     */
    public function __construct(callable $callable)
    {
        $this->callable = $callable;
    }

    /**
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function execute(ServerRequestInterface $request): ResponseInterface
    {
        return call_user_func($this->callable, $request);
    }
}