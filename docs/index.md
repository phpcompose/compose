# Compose Documentation

📖 **Online Documentation:** https://phpcompose.github.io/compose/

Welcome to the Compose framework documentation. Compose is a lightweight PHP framework built around **Pages** — a filesystem-driven URL-to-page mapping engine with optional code-behind support. Pages are the primary application surface, allowing you to map request paths directly to page templates with minimal configuration.

## Why Pages?

Traditional MVC frameworks require you to define routes, create controllers, and wire them together before you can see a single page. Compose takes a different approach: **your filesystem IS your routing**. Create a file, and it's instantly accessible at the corresponding URL.

This Pages-first approach offers several advantages:

- **Faster Development**: Scaffold new pages without ceremony—just create a file
- **Intuitive Structure**: URLs map 1:1 to files, making it easy to find code
- **Progressive Enhancement**: Start with static templates, add logic only when needed
- **Flexible Architecture**: Pages work alongside traditional routes and controllers when you need them

While Compose supports traditional routing and controllers, Pages are the recommended starting point for most applications. They provide the simplicity of static sites with the power of dynamic server-side logic.

## Quick Navigation

- **[Conceptual Overview](conceptual-overview.md)** – Understand how configuration, pipeline, pages, and events fit together
- **[Getting Started](getting-started.md)** – Install the framework and create your first page-based application
- **[Views & Pages](views-and-pages.md)** – Deep dive into the Pages middleware, code-behind scripts, and template rendering
- [Configuration Reference](configuration.md) – Detailed explanation of the configuration array
- [HTTP Pipeline](http-pipeline.md) – How middleware, routing, and events fit together
- [Service Container](service-container.md) – Registering services, factories, and leveraging autowiring

## What is the Pages Middleware?

The Pages middleware is Compose's core feature and the **primary way to build applications**. It automatically maps URL paths to template files in your `pages/` directory, eliminating the need for route definitions:

- **`/`** → `pages/index.phtml`
- **`/about`** → `pages/about.phtml`
- **`/blog/post`** → `pages/blog/post.phtml`

This convention-over-configuration approach means you can scaffold new pages simply by creating files. The URL structure mirrors your filesystem, making applications intuitive to navigate and maintain.

### Key Features

**Filesystem-Based Routing**: No route definitions required. The directory structure directly maps to URLs, making it intuitive to organize your application. Add a new page by creating a new file—no configuration needed.

**Code-Behind Support**: Place a `.phtml.php` file alongside your template to add server-side logic. The code-behind can return data for rendering, accept URL parameters, or return a full PSR-7 response. This keeps page logic close to the template while maintaining clean separation of concerns.

**Zero Configuration Start**: With sensible defaults, you can start building pages immediately. The framework handles template resolution, parameter extraction, and rendering automatically. Advanced configuration is available when you need it.

**PSR-15 Compatible**: Pages middleware integrates seamlessly with the PSR-15 middleware pipeline, allowing you to add authentication, caching, logging, or any other middleware around your pages. Pages are just middleware, so they compose naturally with the rest of your application.

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

Once you've created your first application, dive deeper into the Pages system with the **[Views & Pages](views-and-pages.md)** guide. It covers:

- Complete file mapping rules and template resolution
- Code-behind signatures, invocation, and return types
- URL parameter extraction and type hints
- Pipeline integration and middleware composition
- Configuration options for organizing large applications
- Best practices for structuring page-based applications

## 1.0.0 RC1

Compose 1.0.0-rc1 finalizes the refreshed configuration story, improved middleware orchestration, and enhanced pages module introduced during the 1.0 development cycle. All examples in this documentation assume the 1.0.0-rc1 build unless noted otherwise.
