# Configuration Reference

Compose uses a single associative array (or `Compose\Support\Configuration`) to describe the application. The configuration is stored in the service container and consumed by factories across the framework. This document explains the supported keys and how they affect the runtime.

## Loading Configuration

```php
use Compose\Config;
use Compose\Support\Configuration;

$defaults = (new Config())();
$config = new Configuration($defaults);
$config->merge(require __DIR__ . '/app.local.php');
```

`Configuration` extends `ArrayObject`, exposes helper methods such as `mergeFromFile()` and `getNestedValue()`, and can be marked read-only to guard against accidental mutations.

## Top-Level Keys

| Key | Type | Description |
| --- | --- | --- |
| `app` | array | Arbitrary metadata displayed in templates or logs. |
| `debug` | bool | Enables verbose error output when true. |
| `services` | array | Service definitions registered in the container. |
| `middleware` | array | Ordered pipeline middleware list (sorted with `ksort`). |
| `templates` | array | View engine configuration (folders, layout, helpers, etc.). |
| `pages` | array | Settings consumed by the Pages middleware. |
| `routes` | array | Map of path => handler for routing. |
| `subscribers` | array | Event subscribers (see [HTTP Pipeline](http-pipeline.md)). |
| `error_listeners` | array | Listeners for the error handler. |

### `services`

Service definitions support multiple formats:

- **Class name**: the container will lazily instantiate the class (`'logger' => Monolog\Logger::class`).
- **Callable factory**: receives the container and service id.
- **Object instance**: stored as a shared singleton.
- **Null or same id**: treated as an alias to the id (`SomeInterface::class => SomeImplementation::class`).

Services that implement `Compose\Container\ResolvableInterface` are eligible for autowiring: the container can instantiate them on demand by resolving constructor dependencies via type hints.

```php
'services' => [
    Psr\Log\LoggerInterface::class => function ($container) {
        return new Monolog\Logger('app');
    },
    App\Http\HomeAction::class, // autowire class implementing ResolvableInterface
],
```

### `middleware`

Middleware definitions mirror the service patterns:

- Class or service id (`Compose\Http\OutputBufferMiddleware::class`).
- Callable that returns a response or delegator.
- Instance implementing `MiddlewareInterface`.
- Nested arrays, which become `MiddlewarePipe` instances.

Only numeric keys are necessary; they are sorted to control execution order:

```php
'middleware' => [
    10 => Compose\Http\OutputBufferMiddleware::class,
    20 => Compose\Http\BodyParsingMiddleware::class,
    90 => Compose\Mvc\MvcMiddleware::class,
],
```

### `templates`

The view engine is backed by Plates. Supported keys include:

- `dir`: Base directory for templates (`COMPOSE_DIR_TEMPLATE` by default).
- `folders`: Named folders mapped to absolute paths.
- `maps`: Override template resolution (`'error' => __DIR__ . '/../templates/error'`).
- `layout`: Default layout alias (`layouts::app`).
- `extension`: Template file extension (defaults to `phtml`).
- `helpers`: Array of helper providers or alias => definition pairs.

### `pages`

Configure the Pages middleware, which renders templates based on the request path:

- `dir`: Directory that houses `*.phtml.php` pages.
- `folders`: Additional named directories that can be referenced via `alias::template`.
- `default_page`: Name of the fallback script (`index` by default).

### `routes`

Routes wire specific paths to handlers. Each entry is consumed by `MvcMiddleware` and the routing middleware:

```php
'routes' => [
    '/ping' => App\Http\PingAction::class,
    '/hello' => [App\Http\HelloAction::class, 'handle'],
],
```

Handlers can be class names, callables, or pre-built middleware instances. For complex routing structures, register routes programmatically inside a bootstrapping callback (see [HTTP Pipeline](http-pipeline.md#customizing-the-pipeline)).

### `subscribers` and Events

Subscribers listen for framework events such as `PipelineInitEvent`, `PipelineReadyEvent`, `DispatchEvent`, and custom broadcasts (e.g., `BroadcastEvent('pages.match')`). Each subscriber must implement `Compose\Event\SubscriberInterface` and register listeners through the provided methods.

### `error_listeners`

Attach additional listeners to the Laminas error handler, for example to log exceptions or convert errors into custom responses. Each entry can be a service id, class name, or callable.

## Environment-Specific Overrides

Compose does not impose a specific configuration structure, but it is common to merge per-environment overrides:

```php
$config = new Configuration((new Config())());
$config->mergeFromFile(__DIR__ . '/app.global.php');

if (file_exists(__DIR__ . '/app.' . getenv('APP_ENV') . '.php')) {
    $config->mergeFromFile(__DIR__ . '/app.' . getenv('APP_ENV') . '.php');
}
```

Because `Configuration` uses Laminas `ArrayUtils::merge`, nested arrays are merged recursively, preserving numeric keys where appropriate.
