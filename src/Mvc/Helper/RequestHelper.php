<?php

namespace Compose\Mvc\Helper;

use Compose\Mvc\HelperRegistryInterface;
use Psr\Http\Message\ServerRequestInterface;

class RequestHelper implements HelperRegistryAwareInterface
{
    private ?HelperRegistryInterface $registry = null;
    private ?ServerRequestInterface $request = null;

    public function setHelperRegistry(HelperRegistryInterface $registry): void
    {
        $this->registry = $registry;
    }

    public function __invoke(...$args)
    {
        return $this;
    }

    public function request(): ServerRequestInterface
    {
        if ($this->request instanceof ServerRequestInterface) {
            return $this->request;
        }

        $current = $this->registry ? $this->registry->getCurrentRequest() : null;
        if ($current instanceof ServerRequestInterface) {
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
