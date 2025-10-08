# Compose Framework

Compose is a lightweight PHP framework that combines a PSR-15 middleware pipeline, a pragmatic service container, and a first-class Pages system. The framework focuses on getting you from configuration to a running HTTP application quickly, while staying close to well-known PSR standards. Version 1.0.0-rc1 locks in the middleware orchestration, eventing model, and the Pages workflow iterated during the 1.0 development cycle.

## Highlights
- Built on Laminas Stratigility and Diactoros for PSR-7/PSR-15 compatibility.
- Service container with constructor injection, factories, and autowiring for classes that implement `Compose\Container\ResolvableInterface`.
- Event-driven HTTP pipeline with hooks for initialization, dispatch, and response handling.
- Page-driven MVC with the Pages middleware (the core feature). Build apps by composing filesystem pages first; fall back to controllers and routes when needed.
- Sensible defaults with opt-in configuration overrides.

## Requirements
- PHP 8.3 or newer.
- Composer for dependency installation.

## Installation

Install the framework as a project dependency:

```bash
composer require phpcompose/compose:^1.0@rc
```

> **Versioning**: The `^1.0@rc` constraint tracks the 1.0.0 release candidate series. Once 1.0.0 ships, switch to `^1.0` to follow the stable line.

## Quick Start

The quickest way to see Compose in action is to bootstrap the starter pipeline with a small configuration array. This example renders a hello world page using the built-in Plates view integration.

1. Create a `public/index.php` front controller (quick demo with inline config). Use `Starter::start()` to launch the app:

    ```php
    <?php
    declare(strict_types=1);

    use Compose\Starter;

    require __DIR__ . '/../vendor/autoload.php';

    // Inline quick-demo configuration (merge over framework defaults)
    $config = array_replace_recursive((new \Compose\Config())(), [
        'app' => [
            'name' => 'Hello Compose',
        ],
        'templates' => [
            'dir' => __DIR__ . '/../templates',
        ],
        'pages' => [
            'dir' => __DIR__ . '/../pages',
        ],
    ]);

    Starter::start($config);
    ```


2. Add a page under `pages/index.phtml`. The request path `/` maps directly to `pages/index.phtml`, `/about` would map to `pages/about.phtml`, and subfolders follow the URI hierarchy (e.g. `/docs/intro` → `pages/docs/intro.phtml`).

    ```php
    <!-- pages/index.phtml -->
    <h1><?= $this->e($title ?? 'Hello Compose') ?></h1>
    <p><?= $this->e($message ?? 'Pages are rendered straight from the filesystem.') ?></p>
    ```

    Optionally, place a `pages/index.phtml.php` file next to the template to provide a code-behind function. When present it receives the current request (and any path parameters), returning data that is passed to the template:

    ```php
    <?php
    use Psr\Http\Message\ServerRequestInterface;

    return static function (ServerRequestInterface $request): array {
        return [
            'title' => 'Welcome to Compose',
            'message' => 'Served by the Pages middleware.',
        ];
    };
    ```

3. Serve the application locally:

    ```bash
    php -S 0.0.0.0:8080 -t public/
    ```

Visit `http://localhost:8080` and you should see the rendered message. The Pages middleware resolves templates directly from the `pages/` directory, applies namespace folders when configured, and falls back to routing/dispatch middleware if no page matches.

## Configuration Overview

Configuration is provided as an array (or `Compose\Support\Configuration`) and merged into the dependency container. Some of the most common keys include:

- `debug`: Toggles verbose error output.
- `services`: Map of service identifiers to class names, factories, callables, or instances.
- `middleware`: Ordered list of middleware to pipe into the HTTP pipeline.
- `templates`: View engine configuration (folders, layout, helpers, etc.).
- `pages`: Directory that holds page actions (`*.phtml.php`).
- `routes`: Route map passed to the routing middleware.
- `subscribers`: Event subscribers that listen to framework events.

See the `docs/` directory for in-depth explanations and additional recipes.

## Running Tests

```bash
composer test
```

This uses PHPUnit with the project-provided bootstrap.

## Documentation

Extended documentation for 1.0.0-rc1 lives under [`docs/`](docs/index.md). The markdown files are ready for GitHub Pages or any static site generator.
