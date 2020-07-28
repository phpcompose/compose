<?php


namespace Compose\Mvc\Helper;


use Psr\Http\Message\ServerRequestInterface;

class RequestHelper
{
    /**
     * setter inject
     * @var HelperRegistry
     */
    public $registry;

    /**
     * @return ServerRequestInterface
     */
    public function request(string $key, $default = null) : ServerRequestInterface
    {
        return $this->registry->currentRequest;
    }

    /**
     * @param string $key
     * @param null $default
     * @return |null
     */
    public function query(string $key, $default = null)
    {
        return $this->request()->getQueryParams()[$key] ?? $default;
    }

    /**
     * @param string $key
     * @param null $default
     * @return |null
     */
    public function post(string $key, $default = null)
    {
        return $this->request()->getParsedBody()[$key] ?? $default;
    }
}