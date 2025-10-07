<?php

declare(strict_types=1);

namespace Tests\Routing;

use Compose\Routing\Route;
use Compose\Routing\RoutingMiddleware;
use Laminas\Diactoros\ServerRequest;
use PHPUnit\Framework\TestCase;

final class RoutingMiddlewareTest extends TestCase
{
    public function testRouteMatchingWithParameters(): void
    {
        $middleware = new RoutingMiddleware();

        $middleware->route(Route::fromArray([
            'path' => '/blog',
            'handler' => static function () {},
        ]));

        $route = $middleware->match(new ServerRequest([], [], '/blog/2025/01', 'GET'));

        $this->assertNotNull($route);
        $this->assertSame('blog', $route->path);
        $this->assertSame(['2025', '01'], $route->params);
    }

    public function testRouteMatchingWithEmptyPathReturnsNull(): void
    {
        $middleware = new RoutingMiddleware();

        $route = Route::fromArray([
            'path' => '/',
            'handler' => static function () {},
        ]);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Route path cannot be empty.');

        $middleware->route($route);
    }
}
