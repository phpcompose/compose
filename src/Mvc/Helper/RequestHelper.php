<?php

namespace Compose\Mvc\Helper;

use Psr\Http\Message\ServerRequestInterface;

class RequestHelper implements HelperInterface
{
    /** @var HelperRegistry|null */
    public $registry;

    private ?HelperRegistry $helpers = null;
    private ?ServerRequestInterface $request = null;

    public function __invoke(HelperRegistry $helpers, ...$args)
    {
        $this->helpers = $helpers;
        $this->registry = $helpers; // backward compatibility for legacy helpers
        $this->request = $helpers->getCurrentRequest();

        return $this;
    }

    public function request() : ServerRequestInterface
    {
        if($this->request) {
            return $this->request;
        }

        $current = $this->helpers ? $this->helpers->getCurrentRequest() : null;
        if($current) {
            $this->request = $current;
            return $current;
        }

        throw new \RuntimeException('No active request available for RequestHelper.');
    }

    public function query(string $key, $default = null)
    {
        return $this->request()->getQueryParams()[$key] ?? $default;
    }

    public function post(string $key, $default = null)
    {
        $body = $this->request()->getParsedBody();
        return is_array($body) ? ($body[$key] ?? $default) : $default;
    }
}
