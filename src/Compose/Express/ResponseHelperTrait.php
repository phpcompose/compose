<?php
namespace Compose\Express;
use Psr\Http\Message\ResponseInterface;

/**
 * Created by PhpStorm.
 * User: Alamin
 * Date: 2016-10-19
 * Time: 10:06 PM
 */

trait ResponseHelperTrait
{
    /**
     * @param array $data
     * @param int $status
     * @param array $headers
     * @return ResponseInterface|\Zend\Diactoros\Response\JsonResponse
     */
    public function json(array $data, int $status = 200, array $headers = []) : ResponseInterface
    {
        return new \Zend\Diactoros\Response\JsonResponse($data, $status, $headers);
    }

    /**
     * @param string $html
     * @param int $status
     * @param array $headers
     * @return ResponseInterface|\Zend\Diactoros\Response\HtmlResponse
     */
    public function html(string $html, int $status = 200, array $headers = []) : ResponseInterface
    {
        return new \Zend\Diactoros\Response\HtmlResponse($html, $status, $headers);
    }

    /**
     * @param string $text
     * @param int $status
     * @param array $headers
     * @return ResponseInterface|\Zend\Diactoros\Response\TextResponse
     */
    public function text(string $text, int $status = 200, array $headers = []) : ResponseInterface
    {
        return new \Zend\Diactoros\Response\TextResponse($text, $status, $headers);
    }

    /**
     * @param $uri
     * @param int $status
     * @param array $headers
     * @return ResponseInterface|\Zend\Diactoros\Response\RedirectResponse
     */
    public function redirect($uri, int $status = 302, array $headers = []) : ResponseInterface
    {
        return new \Zend\Diactoros\Response\RedirectResponse($uri, $status, $headers);
    }


    /**
     * @param int $status
     * @param array $headers
     * @return ResponseInterface|\Zend\Diactoros\Response\EmptyResponse
     */
    public function empty(int $status = 204, array $headers = []) : ResponseInterface
    {
        return new \Zend\Diactoros\Response\EmptyResponse($status, $headers);
    }
}