<?php
declare(strict_types=1);

namespace Tests\Support;

use Compose\Container\ServiceContainer;
use Compose\Support\Configuration;
use Compose\Support\Factory\MvcMiddlewareFactory;
use Compose\Routing\RoutingMiddleware;
use Compose\Mvc\MvcMiddleware;
use Compose\Routing\DispatchMiddleware;
use Compose\Mvc\PagesMiddleware;
use PHPUnit\Framework\TestCase;

final class MvcMiddlewareFactoryTest extends TestCase
{
    public function testRoutesAreRegisteredOnRoutingMiddleware(): void
    {
        $routes = [
            '/a' => 'HandlerA',
            '/b' => 'HandlerB',
        ];

        $config = new Configuration(['routes' => $routes]);
        $container = new ServiceContainer();
        $container->set(Configuration::class, $config);

        // Create a fake routing middleware that records route() calls
        $routing = new class implements \Psr\Http\Server\MiddlewareInterface {
            public array $routed = [];
            public function setContainer($c): void { }
            public function route($route): void { $this->routed[] = $route; }
            public function process(\Psr\Http\Message\ServerRequestInterface $request, \Psr\Http\Server\RequestHandlerInterface $handler): \Psr\Http\Message\ResponseInterface
            {
                return $handler->handle($request);
            }
        };

        // Minimal dispatcher and pages middleware fakes (implement MiddlewareInterface)
        $dispatcher = new class implements \Psr\Http\Server\MiddlewareInterface {
            public function setContainer($c): void { }
            public function process(\Psr\Http\Message\ServerRequestInterface $request, \Psr\Http\Server\RequestHandlerInterface $handler): \Psr\Http\Message\ResponseInterface
            {
                return $handler->handle($request);
            }
        };
        $pages = new class implements \Psr\Http\Server\MiddlewareInterface {
            public function setContainer($c): void { }
            public function setDirectory($dir, $ns = null): void {}
            public function setFolders($folders): void {}
            public function process(\Psr\Http\Message\ServerRequestInterface $request, \Psr\Http\Server\RequestHandlerInterface $handler): \Psr\Http\Message\ResponseInterface
            {
                return $handler->handle($request);
            }
        };

        $container->set(RoutingMiddleware::class, fn() => $routing);
        $container->set(DispatchMiddleware::class, fn() => $dispatcher);
        $container->set(PagesMiddleware::class, fn() => $pages);

        // Run factory
        $mvc = MvcMiddlewareFactory::create($container, MvcMiddleware::class);

        // Assert routing middleware was called with two Route objects
        $this->assertCount(2, $routing->routed);
        $first = $routing->routed[0];
        $second = $routing->routed[1];

        $this->assertEquals('/a', $first->path);
        $this->assertEquals('HandlerA', $first->handler);

        $this->assertEquals('/b', $second->path);
        $this->assertEquals('HandlerB', $second->handler);

        $this->assertInstanceOf(MvcMiddleware::class, $mvc);
    }
}
