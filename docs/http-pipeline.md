# HTTP Pipeline

Compose builds on Laminas Stratigility to assemble a PSR-15 middleware pipeline. The `Compose\Starter` helper wires the default stack, dispatches framework events, and starts the request handler runner.

## Default Stack

`Starter::start($config)` wires the following middleware in order:

1. `Compose\Http\OutputBufferMiddleware` – ensures buffered output is captured.
2. `Laminas\Stratigility\Middleware\OriginalMessages` – preserves the original request/response objects.
3. `Compose\Http\BodyParsingMiddleware` – parses JSON, form-encoded, and XML payloads.
4. `middleware` configuration entries (`ksort` order).
5. `Compose\Mvc\MvcMiddleware` – hosts routing, pages, and MVC-specific pipes.
6. `Compose\Support\Error\NotFoundMiddleware` – renders a not-found response when nothing handled the request.

An error handler (`Laminas\Stratigility\Middleware\ErrorHandler`) is registered before any of the above, and the pipeline is executed inside `Laminas\HttpHandlerRunner\RequestHandlerRunner` with a fallback that renders exceptions via `Compose\Support\Error\ErrorResponseGenerator`.

## Adding Middleware

Add middleware to the `middleware` configuration key. Definitions can be class names, service ids, callables, or instances:

```php
'middleware' => [
    40 => App\Http\AuthenticationMiddleware::class,
    50 => static function ($request, $handler) {
        return $handler->handle($request->withAttribute('start_time', microtime(true)));
    },
],
```

Numeric keys control ordering; higher values run later.

You can also manipulate the pipeline programmatically using the optional callback passed to `Starter::start`:

```php
use Compose\Http\Pipeline;
use Compose\Starter;
use Psr\Container\ContainerInterface;

Starter::start($config, function (ContainerInterface $container, Pipeline $pipeline): void {
    $pipeline->pipeMany([
        App\Http\ProfilingMiddleware::class,
    ]);
});
```

## Routing and Dispatch

The MVC middleware composes the routing stack:

- `Compose\Routing\RoutingMiddleware` matches the incoming request path against the configured routes and attaches a `Route` attribute to the request.
- `Compose\Routing\DispatchMiddleware` resolves the matched route handler, dispatches the request, and emits events for `DispatchEvent`, `ResponseEvent`, and `ExceptionEvent`.
- `Compose\Mvc\PagesMiddleware` attempts to render page templates before delegating to the router.

Routes can be defined declaratively via configuration or programmatically from the callback above (retrieve `RoutingMiddleware` from the container and call `route()`).

## Event Flow

Events are dispatched through `Compose\Event\EventDispatcher` at key points:

- `PipelineInitEvent(ContainerInterface $container)` – after the container and pipeline are created but before middleware is piped.
- `PipelineReadyEvent(ContainerInterface $container)` – once the pipeline is fully composed and ready to handle requests.
- `RouteEvent(ServerRequestInterface $request)` – whenever the routing middleware receives a request.
- `DispatchEvent(Route $route, ServerRequestInterface $request)` – right before a matched route handler is invoked.
- `ResponseEvent(ResponseInterface $response)` – immediately after a route handler returns a response.
- `ExceptionEvent(Throwable $exception)` – when a route handler throws.
- `BroadcastEvent('pages.match')` – emitted by the Pages middleware when a matching page is found.

Register subscribers under the `subscribers` configuration key or use the callback to add listeners manually:

```php
Starter::start($config, function (ContainerInterface $container) {
    $provider = $container->get(Psr\EventDispatcher\ListenerProviderInterface::class);
    $provider->addListener(Compose\Http\Event\ResponseEvent::class, static function ($event) {
        // inspect or mutate the response
    });
});
```

## Error Handling

Compose wraps the pipeline in Laminas' error handling middleware. Configure the error renderer by overriding the `template` map for error views or by adding entries to `error_listeners`. In debug mode (`debug => true`) the framework renders a verbose error template from `templates/error/debug.phtml`.

If no middleware generates a response, `NotFoundMiddleware` produces a 404 page. Customize it by providing your own middleware via configuration or by replacing the service mapping for `Compose\Support\Error\NotFoundMiddleware`.
