<?php

namespace Compose\Http\Event;

use Psr\Http\Message\ResponseInterface;

final class ResponseEvent
{
    public function __construct(private ResponseInterface $response) {}

    public function response(): ResponseInterface
    {
        return $this->response;
    }
}
