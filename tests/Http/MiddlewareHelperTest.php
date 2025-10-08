<?php
namespace Tests\Http;

// ensure pipeline helper function is loaded
require_once __DIR__ . '/../../src/Http/Pipeline.php';

use PHPUnit\Framework\TestCase;
use Compose\Http\middleware;
use Compose\Container\ServiceContainer;
use Psr\Http\Server\MiddlewareInterface;

class MiddlewareHelperTest extends TestCase
{
    public function testStringResolvesToResolvableMiddleware()
    {
        $container = new ServiceContainer();
    $mw = \Compose\Http\middleware(\Compose\Http\Pipeline::class, $container);
        $this->assertInstanceOf(MiddlewareInterface::class, $mw);
    }

    public function testCallableWrapped()
    {
        $container = new ServiceContainer();
    $mw = \Compose\Http\middleware(function($req, $handler) { return $handler->handle($req); }, $container);
        $this->assertInstanceOf(MiddlewareInterface::class, $mw);
    }

    public function testObjectMiddlewarePassThrough()
    {
        $container = new ServiceContainer();
        $obj = new class implements MiddlewareInterface {
            public function process(\Psr\Http\Message\ServerRequestInterface $request, \Psr\Http\Server\RequestHandlerInterface $handler): \Psr\Http\Message\ResponseInterface
            {
                return $handler->handle($request);
            }
        };
    $mw = \Compose\Http\middleware($obj, $container);
        $this->assertSame($obj, $mw);
    }

    public function testArrayBecomesPipe()
    {
        $container = new ServiceContainer();
    $mw = \Compose\Http\middleware([function($r,$h){return $h->handle($r);} , function($r,$h){return $h->handle($r);} ], $container);
        $this->assertInstanceOf(MiddlewareInterface::class, $mw);
    }
}
