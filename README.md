# Compose Framework

Compose is a lightweight PHP framework that combines a PSR-15 middleware pipeline, a pragmatic service container, and a Plates-powered view layer. The framework focuses on getting you from configuration to a running HTTP application quickly, while staying close to well-known PSR standards. Version 1.0.0-rc1 locks in the middleware orchestration, eventing model, and pages workflow iterated during the 1.0 development cycle.

## Highlights
- Built on Laminas Stratigility and Diactoros for PSR-7/PSR-15 compatibility.
- Service container with constructor injection, factories, and autowiring for classes that implement `Compose\Container\ResolvableInterface`.
- Event-driven HTTP pipeline with hooks for initialization, dispatch, and response handling.
- Page-driven MVC support with Plates templates, layouts, helpers, and view composition baked in.
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

1. Create a `public/index.php` front controller (quick demo with inline config):

    ```php
    <?php
    declare(strict_types=1);

    use Compose\Starter;

    require __DIR__ . '/../vendor/autoload.php';

    // Inline quick-demo configuration (merge over framework defaults)
    $base = (new \Compose\Config())();

    $config = array_replace_recursive($base, [
        'app' => [
            'name' => 'Hello Compose',
        ],
        'templates' => [
            'layout' => 'layouts::app',
            'folders' => [
                'layouts' => __DIR__ . '/../layouts',
            ],
        ],
        'pages' => [
            'dir' => __DIR__ . '/../pages',
        ],
    ]);

    Starter::start($config);
    ```


2. Create a simple layout and page (same as before):

    - `layouts/app.phtml`
        ```php
        <!doctype html>
        <html lang="en">
        <head>
            <meta charset="utf-8">
            <title><?= $this->e($title ?? 'Compose App') ?></title>
        </head>
        <body>
            <?= $this->section('content') ?>
        </body>
        </html>
        ```

    - `pages/index.phtml.php`
        ```php
        <?php
        use Psr\Http\Message\ServerRequestInterface;

        return static function (ServerRequestInterface $request): array {
            return [
                'title' => 'Welcome to Compose',
                'message' => 'Hello from the pipeline!',
            ];
        };
        ```

3. Serve the application locally:

    ```bash
    php -S 0.0.0.0:8080 -t public/
    ```

Visit `http://localhost:8080` and you should see the rendered message. The starter automatically wires the HTTP pipeline, error handling middleware, page middleware, and template engine.

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
