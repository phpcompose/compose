<?php
namespace Compose\Mvc;
use Compose\Common\ServiceContainerAwareInterface;
use Psr\Http\Message\ResponseInterface;
use Zend\Expressive\Router\RouteResult;
use Zend\Expressive\Router\RouterInterface;

/**
 * Created by PhpStorm.
 * User: Alamin
 * Date: 2016-10-19
 * Time: 10:06 PM
 */

trait ResponseHelperTrait
{
    /**
     * @param string $template
     * @param array $data
     * @param int $status
     * @param array $headers
     * @return ResponseInterface|\Zend\Diactoros\Response\HtmlResponse
     * @throws \Exception
     * @internal param $model
     */
    public function view(string $template, array $data = [], int $status = 200, array $headers = []) : ResponseInterface
    {
        if(!$this instanceof ServiceContainerAwareInterface) {
            throw new \Exception("Class using trait ResponseHelperTrait must implement ServiceContainerAwareInterface to use view() method.");
        }

        /** @var \Zend\Expressive\Template\TemplateRendererInterface $renderer */
        $renderer = $this->getServiceContainer()->get(\Zend\Expressive\Template\TemplateRendererInterface::class);
        if(!$renderer) {
            throw new \Exception("TemplateRendererInterface not found in the container.");
        }

        // now need to guess template script based on current request
        // /app/some/path/write => App\WriteAction::class                           = app::write
        // /app/some/path/write => App\Action\HandlerAction::class                  = app::handler
        // /app/some/path/blog/read => Abc\Controller\BlogController::class:read    = abc::blog\read
        return $this->html($renderer->render($template, $data), $status, $headers);
    }

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

    /**
     *
     */
    public function error()
    {

    }
}