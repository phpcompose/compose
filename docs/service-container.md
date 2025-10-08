# Service Container

Compose ships with a lightweight dependency injection container designed specifically for the framework. It implements `Psr\Container\ContainerInterface`, supports autowiring through reflection, and integrates tightly with the middleware pipeline.

## Basics

The container is created in `Compose\Starter::init()` and seeded with:

- The configuration array (`Compose\Support\Configuration`).
- Service definitions from `config['services']`.
- Framework defaults defined in `Compose\Config`.

Retrieve the container during bootstrapping by using the optional callback:

```php
use Compose\Http\Pipeline;
use Compose\Starter;
use Psr\Container\ContainerInterface;

Starter::start($config, function (ContainerInterface $container, Pipeline $pipeline): void {
    $logger = $container->get(Psr\Log\LoggerInterface::class);
    // ...
});
```

## Defining Services

Register services by updating the `services` configuration key. Supported definitions:

| Definition Type | Example | Notes |
| - | - | - |
| Class name | `Compose\Http\OutputBufferMiddleware::class` | Instantiated lazily and treated as a shared singleton. |
| Callable factory | `static fn ($container) => new Logger('app')` | Receives the container and service id. |
| Object instance | `new Monolog\Logger('app')` | Stored exactly as provided. |
| Alias | `SomeInterface::class => SomeImplementation::class` | Resolves the alias when requested. |

Use `setMany()` for programmatic registration:

```php
$container = new Compose\Container\ServiceContainer();
$container->setMany([
    Psr\Log\LoggerInterface::class => App\LoggerFactory::class,
    App\Service\Emailer::class,
]);
```

## Autowiring

Classes that implement `Compose\Container\ResolvableInterface` opt into autowiring. When the container needs to instantiate such a class, it delegates to `Compose\Container\ServiceResolver`, which:

1. Reflects the constructor parameters.
2. Resolves type-hinted dependencies from the container.
3. Uses default values for optional parameters.

If the container cannot resolve a required dependency, it throws a descriptive `Compose\Container\ContainerException`.

### Service Factories

For complex services that need full control over instantiation, implement `Compose\Container\ServiceFactoryInterface`:

```php
final class CacheFactory implements Compose\Container\ServiceFactoryInterface
{
    public static function create(ContainerInterface $container, string $id): object
    {
        return new App\Cache(
            $container->get(Redis::class),
            ttl: (int) $container->get(Configuration::class)->getNestedValue('cache.ttl', 60)
        );
    }
}
```

Register the factory in configuration:

```php
'services' => [
    App\Cache::class => App\Factory\CacheFactory::class,
];
```

## Invoking Callables

`ServiceResolver` also supports invoking arbitrary callables via `invoke()` by resolving dependencies from type hints. This is used internally by the Pages middleware but is available for your own factories or listeners.

```php
$resolver = $container->get(Compose\Container\ServiceResolver::class);
$resolver->invoke(static function (Psr\Log\LoggerInterface $logger) {
    $logger->info('Invoked via resolver');
});
```

## Container Awareness

Services that need the container instance can implement `Compose\Container\ContainerAwareInterface`. The resolver automatically calls `setContainer()` after instantiation.

```php
final class ReportGenerator implements ContainerAwareInterface, ResolvableInterface
{
    use Compose\Container\ContainerAwareTrait;

    public function generate(): array
    {
        $config = $this->getContainer()->get(Compose\Support\Configuration::class);
        // ...
    }
}
```

## Error Handling

`ServiceContainer` wraps resolution errors in descriptive exceptions that include the service id and parameter name. When debugging resolution issues:

- Ensure the service is registered or implements `ResolvableInterface`.
- Verify constructor type hints align with registered services.
- Check for circular dependencies.

Enable the `debug` flag in configuration to get additional stack traces through the error handler.
