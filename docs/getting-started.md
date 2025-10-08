# Getting Started

This guide walks through installing Compose, bootstrapping the starter pipeline, and serving a simple page. It assumes a clean directory and access to PHP 8.3 with Composer installed.

## Prerequisites

- PHP 8.3 or newer and the CLI `php` executable available on your `PATH`.
- Composer v2.
- Web server or PHP's built-in development server.

## 1. Install the Framework

Create a new project directory and require Compose:

```bash
mkdir my-compose-app
cd my-compose-app
composer init --name="acme/hello-compose" --require="php:~8.3" --quiet
composer require phpcompose/compose:^1.0@rc
```

Composer will install the framework along with Laminas PSR-7 components and League Plates.

## 2. Bootstrap the Front Controller

Create a `public/` directory with an `index.php` file. This is the entry point that bootstraps the pipeline.

```php
<?php
declare(strict_types=1);

use Compose\Starter;

require __DIR__ . '/../vendor/autoload.php';

$config = require __DIR__ . '/../config/app.php';

Starter::start($config);
```

The starter builds an instance of `Compose\Http\Pipeline`, pipes the default middleware stack, and starts listening for requests.

## 3. Define Configuration

Next, add a `config/app.php` file. Start with the baseline framework configuration and merge your overrides:

```php
<?php
declare(strict_types=1);

use Compose\Config;

$base = (new Config())();

return array_replace_recursive($base, [
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
```

Configuration is stored as an array (or `Compose\Support\Configuration` instance) and injected into the service container. You can override services, middleware, routes, subscribers, and view engine settings the same way.

## 4. Add Layouts and Pages

Compose ships with a Pages middleware that can render Plates templates and optional code-behind scripts. Add the following files:

`layouts/app.phtml`

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

`pages/index.phtml.php`

```php
<?php
use Psr\Http\Message\ServerRequestInterface;

return static function (ServerRequestInterface $request): array {
    return [
        'title' => 'Compose Demo',
        'message' => 'Hello from the Pages middleware!',
    ];
};
```

The `.phtml.php` suffix lets you keep the view template and an optional script together. When the page file returns an array, the value is passed to the template as data. Returning a `ResponseInterface` short-circuits the rendering pipeline.

## 5. Serve the Application

Run the built-in PHP development server from the project root:

```bash
php -S 0.0.0.0:8080 -t public/
```

Visit `http://localhost:8080` to see the app. The starter wires the following middleware by default:

1. Output buffering middleware.
2. Laminas `OriginalMessages`.
3. JSON/form body parsing.
4. Application middleware stack (`middleware` config key).
5. MVC middleware with routing and pages support.
6. Not Found middleware.

## 6. Where to Go Next

- Learn about configuration options in the [Configuration Reference](configuration.md).
- Explore how to register middleware and listen for pipeline events in [HTTP Pipeline](http-pipeline.md).
- Dive into view rendering patterns in [Views & Pages](views-and-pages.md).
- Register services and autowire dependencies via the [Service Container](service-container.md).
