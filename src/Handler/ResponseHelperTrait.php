<?php

namespace Compose\Handler;

use Laminas\Diactoros\Response;
use Psr\Http\Message\ResponseInterface;

trait ResponseHelperTrait
{
    /**
     * Recursively ensure data is UTF-8 compatible for JSON output.
     *
     * @param mixed $data
     * @return array
     */
    protected function encodeJson($data): array
    {
        if ($data instanceof \JsonSerializable) {
            $data = $data->jsonSerialize();
        }

        $encoded = [];
        foreach ((array) $data as $key => $val) {
            if (is_array($val)) {
                $encoded[$key] = $this->encodeJson($val);
            } elseif (is_object($val)) {
                $encoded[$key] = $this->encodeJson($val);
            } else {
                $encoded[$key] = is_string($val) ? mb_convert_encoding($val, 'UTF-8') : $val;
            }
        }

        return $encoded;
    }

    public function json(array $data, int $status = 200, array $headers = []): ResponseInterface
    {
        return new Response\JsonResponse($data, $status, $headers);
    }

    public function html(string $html, int $status = 200, array $headers = []): ResponseInterface
    {
        return new Response\HtmlResponse($html, $status, $headers);
    }

    public function text(string $text, int $status = 200, array $headers = []): ResponseInterface
    {
        return new Response\TextResponse($text, $status, $headers);
    }

    public function redirect(string $uri, int $status = 302, array $headers = []): ResponseInterface
    {
        return new Response\RedirectResponse($uri, $status, $headers);
    }

    public function empty(int $status = 204, array $headers = []): ResponseInterface
    {
        return new Response\EmptyResponse($status, $headers);
    }
}
