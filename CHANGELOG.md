# Changelog

All notable changes to this project will be documented in this file.

## [1.0.0-rc1] - 2025-10-08

### Added
- PSR-14 compliant event infrastructure with `Compose\Event\EventDispatcher`, `ListenerProvider`, and HTTP lifecycle events (`PipelineInitEvent`, `PipelineReadyEvent`, `DispatchEvent`, `ResponseEvent`, `ExceptionEvent`, `RouteEvent`, and `BroadcastEvent`).
- Dedicated pipeline hook middleware including `Compose\Http\OutputBufferMiddleware` to guard against stray output and a refactored `PagesMiddleware` capable of folder mounts, namespace aliases, and code-behind execution.
- Plates-powered view engine (`Compose\Mvc\ViewEngine`) with helper registry awareness, new helper contracts, and a bridge namespace for Plates factories.
- Extensive documentation set under `docs/` covering getting started, configuration, pipeline internals, views, and the service container.
- PHPUnit coverage for the revamped middleware, view engine, configuration helpers, routing, and starter wiring.

### Changed
- `Compose\Config` and `Compose\Support\Configuration` now expose recursive merge helpers, dot-notation lookups, and optional read-only enforcement.
- Routing and MVC composition is orchestrated through updated factories that pipe `RoutingMiddleware`, `DispatchMiddleware`, and the new `PagesMiddleware` in a predictable order.
- Service container internals received stronger validation, improved error messages via `ContainerException`, and a simplified `ServiceFactoryInterface::create(ContainerInterface $container, string $id)` signature.
- Session handling moved to `Compose\Http\Session\Session` with safer lifecycle management (cookie configuration, regeneration, and automatic shutdown).
- Error handling and templates were refreshed — the default renderer integrates with the new view engine and emits richer debug output.
- Dependency stack bumped: PSR container v2, Laminas Stratigility 4.x, Laminas Diactoros 3.x, PSR event-dispatcher, and PHPUnit 11.
- Starter now pipes the error handler, emits lifecycle events, and executes optional boot callbacks after the container and pipeline are ready.

### Removed
- Legacy MVC view renderer, helper, and pages classes (`Compose\Mvc\ViewRenderer`, `PagesHandler`, Layout helpers, etc.) replaced by the unified view engine.
- Custom event messaging abstractions (`EventDispatcherInterface`, `Message`, `ExceptionMessage`) superseded by PSR-14 compliant components.
- Outdated application scaffolding under `src/App/` in favor of container-driven factories and the Pages middleware.

### Fixed
- Middleware resolution and invocation paths now throw descriptive exceptions when dependencies cannot be resolved, improving debugging during container configuration.
- Error templates respect layouts and helper context, eliminating inconsistent rendering states present in earlier betas.
