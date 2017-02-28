<?php
/**
 * Created by PhpStorm.
 * User: Alamin
 * Date: 2016-10-19
 * Time: 10:06 PM
 */
namespace Compose\Mvc;


use Compose\System\Container\ContainerAwareInterface;
use Psr\Http\Message\ResponseInterface;
use Zend\Diactoros\Response;


/**
 * Class ResponseHelperTrait
 * @package Compose\Mvc
 */
trait ResponseHelperTrait
{
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

    /**
     * View rendering helper
     *
     * Right now relies on ContainerAwareInterface to get the container and get the View renderer
     * @param string $template
     * @param array $data
     * @param int $status
     * @param array $headers
     * @return ResponseInterface
     * @throws \Exception
     */
    protected function view(string $template, array $data = [], int $status = 200, array $headers = []): ResponseInterface
    {
        if (!$this instanceof ContainerAwareInterface) {
            throw new \Exception("Class using ResponseHelperTrait needs to implement ContainerAwareInterface... " .
                "Need the container to get the view renderer.");
        }

        /** @var ViewRendererInterface $renderer */
        $renderer = $this->getContainer()->get(ViewRendererInterface::class);
        if (!$renderer) {
            throw new \Exception("ViewRendererInterface not found in the container.");
        }

        return $this->html($renderer->render($template, $data), $status, $headers);
    }
}