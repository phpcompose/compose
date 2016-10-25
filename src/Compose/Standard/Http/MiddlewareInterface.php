<?php
/**
 * Created by PhpStorm.
 * User: alaminahmed
 * Date: 2016-10-23
 * Time: 1:24 PM
 */

namespace Compose\Standard\Http;


use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Interface MiddlewareInterface
 *
 * Provides interface for PSR-7 Based middleware
 *
 * @package Compose\Standard\Middleware
 */
interface MiddlewareInterface extends \Zend\Stratigility\MiddlewareInterface
{
    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @param callable|null $next
     * @return ResponseInterface
     */
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, callable $next = null) : ResponseInterface;
}