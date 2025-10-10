<?php

namespace Compose\Routing\Event;

use Compose\Routing\Route;
use Psr\Http\Message\ServerRequestInterface;

final class DispatchEvent
{
    public function __construct(
        private Route $route,
        private ServerRequestInterface $request
    ) {}

    public function route(): Route
    {
        return $this->route;
    }

    public function request(): ServerRequestInterface
    {
        return $this->request;
    }
}
