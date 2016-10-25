<?php
/**
 * Created by PhpStorm.
 * User: alaminahmed
 * Date: 2016-10-24
 * Time: 10:06 PM
 */

namespace Compose\Standard\Http;


use Compose\Standard\Http\Exception\HttpException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
/**
 * Class RestfulResourceTrait
 *
 * Provides implementation of RestfulResourceInterface
 * @package Compose\Standard\Http
 */
trait RestfulResourceTrait
{
    /**
     * @inheritdoc
     * @param RequestInterface $request
     * @return ResponseInterface
     * @throws HttpException
     */
    public function get(ServerRequestInterface $request, $ad= null, ...$params) : ResponseInterface
    {
        throw new HttpException(404);
    }

    /**
     * @inheritdoc
     * @param RequestInterface $request
     * @return ResponseInterface
     * @throws HttpException
     */
    public function list(ServerRequestInterface $request): ResponseInterface
    {
        throw new HttpException(404);
    }

    /**
     * @inheritdoc
     * @param RequestInterface $request
     * @return ResponseInterface
     * @throws HttpException
     */
    public function post(ServerRequestInterface $request): ResponseInterface
    {
        throw new HttpException(404);
    }

    /**
     * @inheritdoc
     * @param RequestInterface $request
     * @return ResponseInterface
     * @throws HttpException
     */
    public function put(ServerRequestInterface $request) : ResponseInterface
    {
        throw new HttpException(404);
    }

    /**
     * @inheritdoc
     * @param RequestInterface $request
     * @return ResponseInterface
     * @throws HttpException
     */
    public function delete(ServerRequestInterface $request): ResponseInterface
    {
        throw new HttpException(404);
    }

}