<?php

namespace Compose\Mvc\Helper;

use Compose\Mvc\HelperRegistryInterface;
use Psr\Http\Message\ServerRequestInterface;

class RequestHelper implements HelperInterface
{
    public $registry;

    private ?HelperRegistryInterface $helpers = null;
    private ?ServerRequestInterface $request = null;

    public function __invoke(HelperRegistryInterface $helpers, ...$args)
    {
        $this->helpers = $helpers;
        $this->registry = $helpers; // backwards compatibility
        $this->request = $helpers->getCurrentRequest();

        return $this;
    }

    public function request(): ServerRequestInterface
    {
        if ($this->request) {
            return $this->request;
        }
        $current = $this->helpers ? $this->helpers->getCurrentRequest() : null;
        if ($current) {
            $this->request = $current;
            return $current;
        }
        throw new \RuntimeException('No active request available for RequestHelper.');
    }

    public function query(string $key, $default = null): mixed
    {
        $params = $this->request()->getQueryParams();
        return array_key_exists($key, $params) ? $params[$key] : $default;
    }

    public function post(string $key, $default = null): mixed
    {
        $body = $this->request()->getParsedBody();
        return is_array($body) && array_key_exists($key, $body) ? $body[$key] : $default;
    }
}
