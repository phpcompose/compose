# Views & Pages

Compose integrates [League Plates](https://platesphp.com/) to render templates and supports a "pages" convention that maps request paths to templates with optional code-behind scripts.

## View Engine Basics

The view engine is exposed via `Compose\Mvc\ViewEngineInterface` and backed by `Compose\Mvc\ViewEngine`. The default factory registers:

- Base directory `templates/` (set via `templates.dir`).
- Named folders defined in `templates.folders` and accessible with the `alias::template` syntax.
- Layout support controlled by `templates.layout`.
- Helper registry populated from `templates.helpers`.

Render a template directly from a handler:

```php
use Compose\Mvc\ViewEngineInterface;
use Laminas\Diactoros\Response\HtmlResponse;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final class HomeAction implements RequestHandlerInterface
{
    public function __construct(private ViewEngineInterface $views) {}

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $html = $this->views->render('home', [
            'title' => 'Compose MVC',
            'message' => 'Rendered from a request handler.',
        ], $request);

        return new HtmlResponse($html);
    }
}
```

## Layouts

Layouts are standard Plates layouts:

```php
<!-- templates/layouts/app.phtml -->
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

Enable a default layout by setting `templates.layout` (`layouts::app` in this example). Override per render call via the fourth argument to `render()`.

## View Helpers

Helpers are registered through the helper registry. Helpers can be:

- A class that exposes multiple helpers via `HelperRegistry::extend()`.
- A keyed entry mapping an alias to a callable or service id.

```php
'templates' => [
    'helpers' => [
        App\View\HelperProvider::class, // extends registry
        'asset' => static fn (string $path) => '/assets/' . ltrim($path, '/'),
    ],
],
```

Within templates, call helpers through `$this->asset('css/app.css')`.

## Pages Middleware

The Pages middleware renders templates based on the request path:

- `/` → `pages/index.phtml` (or `.phtml.php` script).
- `/blog` → `pages/blog/index.phtml`.
- `/blog/post` → `pages/blog/post.phtml`.

The middleware resolves templates by checking:

1. Explicit map overrides (`templates.maps`).
2. Aliased folders (`alias::template`).
3. The base pages directory (`pages.dir`).

### Code-Behind Scripts

If a `.phtml.php` file exists alongside the template, it is included first. The script can return:

- **`array`** – merged into the template data.
- **`callable`** – invoked with `(ServerRequestInterface $request, ...$params)` where the params are derived from the URL segments. The return value is used as the template data.
- **`ResponseInterface`** – returned directly, bypassing template rendering.

Example:

```php
// pages/profile.phtml.php
<?php
use Psr\Http\Message\ServerRequestInterface;

return static function (ServerRequestInterface $request, string $username): array {
    return [
        'title' => 'Profile: ' . $username,
        'username' => $username,
    ];
};
```

And the paired template:

```php
<!-- pages/profile.phtml -->
<?php $this->layout('layouts::app', ['title' => $title]); ?>

<h1><?= $this->e($title) ?></h1>
<p>Hello <?= $this->e($username) ?>!</p>
```

### Additional Folders

Use `pages.folders` to mount additional directories:

```php
'pages' => [
    'dir' => __DIR__ . '/../pages',
    'folders' => [
        'docs' => __DIR__ . '/../content/docs',
    ],
],
```

Requests to `/docs/...` will look inside the mounted folder and can use `docs::template` aliases elsewhere.

## Rendering Outside HTTP

Because the view engine only relies on Plates, you can reuse it in CLI commands, background jobs, or any other context by retrieving `ViewEngineInterface` from the container and calling `render()` directly. Pass `null` as the request when rendering outside HTTP.
