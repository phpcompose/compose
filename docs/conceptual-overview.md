# Conceptual Overview

Compose puts a PSR-15 middleware pipeline, a pragmatic service container, and filesystem-driven pages behind a single entry point. This document connects the major pieces so you can understand how a request turns into a response and where to plug in your own code.

## Big Picture

1. **Configuration** – You prepare an array (or `Compose\Support\Configuration`) that defines services, middleware, template settings, routes, and pages.
2. **Starter** – `Starter::start($config)` creates the service container, assembles the middleware pipeline, dispatches lifecycle events, and begins listening for HTTP requests.
3. **Pipeline** – Incoming requests flow through the pipeline: output buffering, original message preservation, body parsing, user-defined middleware, MVC middleware, and a not-found handler.
4. **Pages First** – The MVC middleware hosts the Pages middleware, routing middleware, and dispatch middleware. Pages try to satisfy a request directly from the filesystem before handing off to controllers or other handlers.
5. **Renderer & Templates** – Templates are rendered with Plates via the template renderer. Helpers, layouts, and template aliases let you organize presentation code without leaving the filesystem-first approach.

Each layer can be customised—swap middleware, override services, add events—without losing the default behaviour that gets you to “Hello World” immediately.

## Request Lifecycle

1. **Bootstrap**
   - `Starter::start` wraps your configuration in `Configuration`, creates a `ServiceContainer`, registers framework defaults, and pipes the error handler.
   - `PipelineInitEvent` fires so listeners can adjust the container before middleware is added.
2. **Pipeline Composition**
   - Output buffering, `OriginalMessages`, and `BodyParsingMiddleware` are piped first.
   - Any middleware declared in `config['middleware']` is sorted (`ksort`) and piped in order.
   - The MVC middleware is piped, followed by `NotFoundMiddleware`.
   - `PipelineReadyEvent` fires, enabling listeners to inspect the fully composed container/pipeline.
3. **Dispatch**
   - The request passes through user middleware, then enters the MVC middleware.
   - Pages middleware tries to resolve a filesystem page; if none match it delegates to routing and dispatch middleware.
   - If a handler returns a response, `ResponseEvent` fires. Exceptions trigger `ExceptionEvent` and ultimately the error handler.
4. **Emission**
   - `RequestHandlerRunner` emits the final response. If no middleware produced one, `NotFoundMiddleware` renders the default 404 page.

## Configuration & Container

`Compose\Config` defines the framework defaults. You merge your overrides on top:

```php
$config = array_replace_recursive((new Compose\Config())(), [
    'pages' => ['dir' => __DIR__ . '/../pages'],
    'template' => [
        'layout' => 'layouts::app',
        'folders' => ['layouts' => __DIR__ . '/../layouts'],
    ],
    'services' => [
        Psr\Log\LoggerInterface::class => App\LoggerFactory::class,
    ],
]);
```

When the container resolves a service it supports:

- Class names that implement `ResolvableInterface` (autowired).
- Callable factories receiving `(ContainerInterface $container, string $id)`.
- Pre-built instances or aliases to other service ids.

Because the configuration object can be toggled read-only, it’s safe to pass around without worrying about accidental mutation.

## Pages-First Rendering

- The filesystem path mirrors the request path. `/` → `pages/index.phtml`, `/docs/intro` → `pages/docs/intro.phtml`.
- Each page can optionally have a sibling `*.phtml.php` script. If present, it runs first and may:
  - Return an array of template data.
  - Return a `ResponseInterface` to short-circuit rendering.
  - Return a callable that receives the request and path parameters.
- Additional directories can be registered through `pages['folders']`, letting you mount sections like `'docs' => __DIR__ . '/../content/docs'`.
- If the Pages middleware cannot find a match, the request continues through routing and dispatch middleware, giving you the flexibility to mix page-based content with controller-driven endpoints.

## Template Aliases & Layouts

The template renderer supports namespaced template references using the `alias::template` syntax:

- The left side (`alias`) refers to a folder alias defined in `template['folders']`.
- The right side (`template`) is the relative path to the script within that folder (extension is appended automatically).
- Example: with `'folders' => ['layouts' => __DIR__ . '/../layouts']`, calling `$this->layout('layouts::app')` loads `layouts/app.phtml`.

If no alias is provided (`'home'` instead of `'app::home'`), the renderer looks in the base directory set by `template['dir']`.

## Events & Subscribers

Compose emits PSR-14 events for key lifecycle moments:

- `PipelineInitEvent` and `PipelineReadyEvent` bookend pipeline composition.
- `RouteEvent`, `DispatchEvent`, `ResponseEvent`, and `ExceptionEvent` cover routing and dispatch.
- `BroadcastEvent('pages.match')` signals when a page successfully matched.

Register subscribers in configuration (`'subscribers' => [App\MySubscriber::class]`) or grab the listener provider in the starter callback to register closures dynamically. Events let you integrate logging, metrics, or feature toggles without modifying core middleware.

## Putting It Together

1. Build your configuration, pointing `pages.dir` at your content directory and registering any services or middleware you need.
2. Start the framework with `Starter::start($config, $optionalCallback)`.
3. Compose pages in the filesystem, using template aliases for layouts and optional code-behind scripts for dynamic behaviour.
4. Layer in middleware, routes, and subscribers when you need advanced behaviour—everything shares the same container and event bus.

Use the other documents for deep dives on specific areas:

- [Getting Started](getting-started.md) – hands-on setup.
- [HTTP Pipeline](http-pipeline.md) – middleware mechanics and events.
- [Views & Pages](views-and-pages.md) – template rendering, helpers, aliases, and code-behind.
- [Configuration Reference](configuration.md) – every configuration key in detail.
- [Service Container](service-container.md) – service registration patterns and autowiring.
