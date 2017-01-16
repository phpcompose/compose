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

/**
 * Interface CommandInterface
 *
 * Provides interface for Command pattern
 * @package Compose\Core\Http
 */
interface CommandInterface
{
    /**
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request) : ResponseInterface;
}