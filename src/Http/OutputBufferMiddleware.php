<?php

namespace Compose\Http;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Guards the pipeline by capturing stray output and surfacing it as a runtime error.
 */
class OutputBufferMiddleware implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $level = ob_get_level();
        ob_start();

        try {
            $response = $handler->handle($request);
        } catch (\Throwable $e) {
            $this->discardOutput($level);
            throw $e;
        }

        $buffer = $this->discardOutput($level);

        if ($buffer !== '') {
            $snippet = substr($buffer, 0, 200);
            error_log("Compose detected unexpected output before response emission: \n" . $snippet);

            throw new \RuntimeException('Unexpected output detected before the response was emitted. Check the error log for the captured bytes.');
        }

        return $response;
    }

    private function discardOutput(int $level): string
    {
        $output = '';

        if (ob_get_level() > $level) {
            $output = ob_get_clean();
        }

        while (ob_get_level() > $level) {
            ob_end_clean();
        }

        return $output;
    }
}
