<?php
/**
 * Created by PhpStorm.
 * User: Alamin
 * Date: 2016-10-19
 * Time: 10:06 PM
 */
namespace Compose\Http;


use Psr\Http\Message\ResponseInterface;
use Laminas\Diactoros\Response;


/**
 * Class ResponseHelperTrait
 * @package Compose\Mvc
 */
trait ResponseHelperTrait
{
    /**
     * @param $data
     * @return array
     */
    protected function encodeJson($data) : array
    {
        $encoded = [];
        foreach ($data as $key => $val) {
            if(is_array($val)) {
                $encoded[$key] = $this->encodeJson($val);
            } else if(is_object($val)) {
                $encoded[$key] = $this->encodeJson($val);
            } else {
                $encoded[$key] = mb_convert_encoding($val, 'UTF-8');
            }
        }

        return $encoded;
    }


    /**
     * @param array $data
     * @param int $status
     * @param array $headers
     * @return ResponseInterface
     */
    public function json(array $data, int $status = 200, array $headers = []) : ResponseInterface
    {
        return new Response\JsonResponse($data, $status, $headers);
    }

    /**
     * @param string $html
     * @param int $status
     * @param array $headers
     * @return ResponseInterface
     */
    public function html(string $html, int $status = 200, array $headers = []) : ResponseInterface
    {
        return new Response\HtmlResponse($html, $status, $headers);
    }

    /**
     * @param string $text
     * @param int $status
     * @param array $headers
     * @return ResponseInterface
     */
    public function text(string $text, int $status = 200, array $headers = []) : ResponseInterface
    {
        return new Response\TextResponse($text, $status, $headers);
    }

    /**
     * @param $uri
     * @param int $status
     * @param array $headers
     * @return ResponseInterface
     */
    public function redirect($uri, int $status = 302, array $headers = []) : ResponseInterface
    {
        return new Response\RedirectResponse($uri, $status, $headers);
    }


    /**
     * @param int $status
     * @param array $headers
     * @return ResponseInterface
     */
    public function empty(int $status = 204, array $headers = []) : ResponseInterface
    {
        return new Response\EmptyResponse($status, $headers);
    }
}