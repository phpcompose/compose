# Compose Framework — Pages-first PHP framework

📖 **Documentation:** https://phpcompose.github.io/compose/

Compose is a lightweight PHP framework built around a Pages feature: a filesystem-driven URL-to-page mapping engine with optional code-behind support. Pages are the primary application surface — map request paths directly to page templates and (optionally) small server-side page handlers that provide data to the templates.

Highlights

- Pages-first design: map /about to pages/about.phtml, /docs/api to pages/docs/api.phtml, and so on.
- Code-behind support: place a pages/<name>.phtml.php next to a template to return data for rendering.
- PSR-15 middleware pipeline and a pragmatic service container.
- Simple configuration with sensible defaults; opt-in overrides when you need them.

Requirements

- PHP 8.3 or newer
- Composer

Installation

Install Compose as a project dependency:

```bash
composer require phpcompose/compose:^1.0@rc
```

Quick Start — Pages and code-behind

1. Create a public/front controller (public/index.php) and boot Compose:

```php
<?php
declare(strict_types=1);

use Compose\Starter;

require __DIR__ . '/../vendor/autoload.php';

$config = array_replace_recursive((new \Compose\Config())(), [
    'app' => ['name' => 'Pages Demo'],
    'template' => ['dir' => __DIR__ . '/../templates'],
    'pages' => ['dir' => __DIR__ . '/../pages'],
]);

Starter::start($config);
```

2. Add templates in pages/ that map to request paths:

- pages/index.phtml -> maps to /
- pages/about.phtml -> maps to /about
- pages/docs/getting-started.phtml -> maps to /docs/getting-started

Example template (pages/index.phtml):

```php
<!-- pages/index.phtml -->
<h1><?= $this->e($title ?? 'Hello Compose') ?></h1>
<p><?= $this->e($message ?? 'Rendered by the Pages middleware.') ?></p>
```

3. Add a code-behind file next to a template to provide data and logic. The code-behind must return a callable that accepts the current PSR-7 ServerRequestInterface and returns an array of template variables.

Example code-behind (pages/index.phtml.php):

```php
<?php
use Psr\Http\Message\ServerRequestInterface;

return static function (ServerRequestInterface $request): array {
    // perform simple logic, read query params or route parameters
    $name = $request->getQueryParams()['name'] ?? null;

    return [
        'title' => 'Welcome to Compose',
        'message' => $name ? "Hello, {$name}!" : 'Served by the Pages middleware with code-behind.',
    ];
};
```

Serving locally

```bash
php -S 0.0.0.0:8080 -t public/
```

Visit http://localhost:8080 and try /, /about, or /?name=Alice.

Pages behavior and routing notes

- Filesystem mapping follows the URI path hierarchy. A request to /foo/bar will look for pages/foo/bar.phtml (and a pages/foo/bar.phtml.php code-behind).
- If a template exists without a code-behind, it is rendered with an empty context (or whatever global view helpers provide).
- Code-behind functions have access to the full ServerRequestInterface and may return path parameters or computed data for templates.
- The Pages middleware is first-class: you can compose middleware around it (authentication, caching, events).

Configuration keys (pages-related)

- pages.dir (string): path to the pages directory (default: pages/)
- pages.namespace (string|array): optional namespace prefix applied to page resolution
- templates.dir (string): directory for templates used by the view engine

Documentation and next steps

The repository includes a docs/ folder with more examples and recipes. For small projects, writing Markdown files under docs/ and enabling GitHub Pages (source: /docs) provides a simple public documentation site. For more advanced sites, scaffold a static docs site using Docusaurus, MkDocs, or similar and deploy to GitHub Pages or another hosting provider.

If you want, I can:
- Open a pull request that updates README.md in your repository with this focused content, or
- Also create a docs/index.md that expands the Pages guide and set up a GitHub Pages-ready docs folder.

License

See the LICENSE file in the repository for license details.
