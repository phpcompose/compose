# Compose Documentation

Welcome to the Compose framework documentation. Compose is a lightweight PHP framework built around **Pages** — a filesystem-driven URL-to-page mapping engine with optional code-behind support. Pages are the primary application surface, allowing you to map request paths directly to page templates with minimal configuration.

## Quick Navigation

- **[Getting Started](getting-started.md)** – Install the framework and create your first page-based application
- **[Views & Pages](views-and-pages.md)** – Deep dive into the Pages middleware, code-behind scripts, and template rendering
- [Configuration Reference](configuration.md) – Detailed explanation of the configuration array
- [HTTP Pipeline](http-pipeline.md) – How middleware, routing, and events fit together
- [Service Container](service-container.md) – Registering services, factories, and leveraging autowiring

## What is the Pages Middleware?

The Pages middleware is Compose's core feature. It automatically maps URL paths to template files in your `pages/` directory:

- **`/`** → `pages/index.phtml`
- **`/about`** → `pages/about.phtml`
- **`/blog/post`** → `pages/blog/post.phtml`

### Key Features

**Filesystem-Based Routing**: No route definitions required. The directory structure directly maps to URLs, making it intuitive to organize your application.

**Code-Behind Support**: Place a `.phtml.php` file alongside your template to add server-side logic. The code-behind can return data for rendering, accept URL parameters, or return a full PSR-7 response.

**Zero Configuration Start**: With sensible defaults, you can start building pages immediately. Advanced configuration is available when you need it.

**PSR-15 Compatible**: Pages middleware integrates seamlessly with the PSR-15 middleware pipeline, allowing you to add authentication, caching, or any other middleware around your pages.

## Common Use Cases

### Simple Static-Like Pages

For content-focused pages without logic, create a template:

```php
<!-- pages/about.phtml -->
<?php $this->layout('layouts::app', ['title' => 'About Us']); ?>

<h1>About Our Company</h1>
<p>We build amazing web applications with Compose.</p>
```

### Dynamic Pages with Code-Behind

Add logic by creating a matching `.phtml.php` file:

```php
// pages/blog/post.phtml.php
<?php
use Psr\Http\Message\ServerRequestInterface;

return static function (ServerRequestInterface $request, string $slug): array {
    $post = fetchPostBySlug($slug); // Your data layer
    
    return [
        'title' => $post->title,
        'post' => $post,
    ];
};
```

### API Endpoints

Return JSON responses directly from code-behind:

```php
// pages/api/users.phtml.php
<?php
use Psr\Http\Message\ServerRequestInterface;
use Laminas\Diactoros\Response\JsonResponse;

return static function (ServerRequestInterface $request): ResponseInterface {
    $users = getAllUsers();
    return new JsonResponse(['users' => $users]);
};
```

## Getting Started

If you're new to Compose, start with the [Getting Started](getting-started.md) guide, which walks you through:

1. Installing the framework
2. Setting up the front controller
3. Creating your first pages
4. Adding layouts and code-behind scripts
5. Serving the application

For a deeper understanding of how Pages work, including advanced features like additional folders, URL parameters, and template resolution, see [Views & Pages](views-and-pages.md).

## 1.0.0 RC1

Compose 1.0.0-rc1 finalizes the refreshed configuration story, improved middleware orchestration, and enhanced pages module introduced during the 1.0 development cycle. All examples in this documentation assume the 1.0.0-rc1 build unless noted otherwise.
