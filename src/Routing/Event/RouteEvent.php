<?php

namespace Compose\Routing\Event;

use Psr\Http\Message\ServerRequestInterface;

final class RouteEvent
{
    public function __construct(
        private ServerRequestInterface $request
    ) {}

    public function request(): ServerRequestInterface
    {
        return $this->request;
    }
}
