# Views & Pages

Compose includes a view engine with helpers and layouts and supports a "pages" convention that maps request paths to templates with optional code-behind scripts. A Plates bridge is available if you prefer Plates APIs.

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

Layouts are standard templates (compatible with the built-in engine and the Plates bridge):

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

The Pages middleware is the primary feature of Compose. It automatically maps URL paths to templates in your filesystem, eliminating the need to define routes for every page. This "convention over configuration" approach makes it fast to scaffold new pages and intuitive to navigate your codebase.

### How URL Mapping Works

The Pages middleware maps request paths to template files based on a simple convention:

- **`/`** → `pages/index.phtml` (the default page)
- **`/about`** → `pages/about.phtml`
- **`/blog`** → `pages/blog/index.phtml` or `pages/blog.phtml`
- **`/blog/post`** → `pages/blog/post.phtml`
- **`/docs/getting-started`** → `pages/docs/getting-started.phtml`

The middleware automatically handles:

- **Index files**: Requests to `/blog` will first check for `pages/blog.phtml`, then fall back to `pages/blog/index.phtml`.
- **Nested paths**: Deep URL structures like `/products/category/item` map to `pages/products/category/item.phtml`.
- **File extensions**: The default extension is `.phtml`, but you can customize this via `templates.extension`.

### Template Resolution Order

When processing a request, the Pages middleware resolves templates by checking in this order:

1. **Explicit map overrides** (`templates.maps`) – Override specific paths
2. **Aliased folders** (`pages.folders`) – Check mounted directories with namespace prefixes
3. **Base pages directory** (`pages.dir`) – The default location for pages

This resolution order allows you to organize content flexibly while maintaining predictable behavior.

### Configuration

Configure the Pages middleware in your `config/app.php`:

```php
'pages' => [
    'dir' => __DIR__ . '/../pages',              // Base directory for pages
    'default_page' => 'index',                    // Name of the default/index page
    'folders' => [                                // Additional mounted directories
        'admin' => __DIR__ . '/../pages-admin',
    ],
],
```

### Code-Behind Scripts

Code-behind scripts bring server-side logic to your pages without requiring separate controller classes. A code-behind file has the same name as your template with an additional `.php` extension: `filename.phtml.php`.

#### Return Types

A code-behind script can return three different types:

**1. Array** – Merged into the template data:

```php
// pages/dashboard.phtml.php
<?php
return [
    'title' => 'Dashboard',
    'stats' => ['users' => 150, 'posts' => 892],
];
```

**2. Callable** – Invoked with the request and URL parameters:

```php
// pages/profile.phtml.php
<?php
use Psr\Http\Message\ServerRequestInterface;

return static function (ServerRequestInterface $request, string $username): array {
    // Access query parameters
    $tab = $request->getQueryParams()['tab'] ?? 'overview';
    
    // Fetch data based on URL parameter
    $user = getUserByUsername($username);
    
    return [
        'title' => 'Profile: ' . $username,
        'user' => $user,
        'activeTab' => $tab,
    ];
};
```

The callable receives:
- `ServerRequestInterface $request` – The PSR-7 request object
- Additional parameters extracted from the URL path (e.g., `/profile/john` passes `"john"` as the second parameter)

**3. ResponseInterface** – Returned directly, bypassing template rendering:

```php
// pages/api/status.phtml.php
<?php
use Psr\Http\Message\ServerRequestInterface;
use Laminas\Diactoros\Response\JsonResponse;

return static function (ServerRequestInterface $request) {
    return new JsonResponse([
        'status' => 'ok',
        'timestamp' => time(),
    ]);
};
```

This is useful for:
- JSON APIs
- File downloads
- Redirects
- Custom response types

#### Complete Example

Here's a full example showing a blog post page with code-behind:

```php
// pages/blog/post.phtml.php
<?php
use Psr\Http\Message\ServerRequestInterface;
use Laminas\Diactoros\Response\RedirectResponse;

return static function (ServerRequestInterface $request, string $slug): mixed {
    $post = findPostBySlug($slug);
    
    // Return 404 redirect if post not found
    if (!$post) {
        return new RedirectResponse('/404');
    }
    
    return [
        'title' => $post->title,
        'post' => $post,
        'relatedPosts' => findRelatedPosts($post->id, 3),
    ];
};
```

And the corresponding template:

```php
<!-- pages/blog/post.phtml -->
<?php $this->layout('layouts::app', ['title' => $title]); ?>

<article>
    <h1><?= $this->e($post->title) ?></h1>
    <div class="content">
        <?= $post->content ?>
    </div>
</article>

<?php if (!empty($relatedPosts)): ?>
    <aside>
        <h2>Related Posts</h2>
        <ul>
            <?php foreach ($relatedPosts as $related): ?>
                <li><a href="/blog/post/<?= $this->e($related->slug) ?>">
                    <?= $this->e($related->title) ?>
                </a></li>
            <?php endforeach; ?>
        </ul>
    </aside>
<?php endif; ?>
```

### URL Parameters

URL segments beyond the template path are passed as parameters to your code-behind callable. This enables clean, RESTful URLs:

```php
// URL: /products/electronics/laptop-123
// File: pages/products.phtml.php

return static function (ServerRequestInterface $request, string $category, string $productId): array {
    return [
        'category' => $category,      // "electronics"
        'productId' => $productId,     // "laptop-123"
    ];
};
```

### Additional Folders

Mount additional page directories using `pages.folders`. This is useful for:

- Separating admin pages from public pages
- Organizing documentation separately
- Multi-tenant applications

Configuration:

```php
'pages' => [
    'dir' => __DIR__ . '/../pages',
    'folders' => [
        'admin' => __DIR__ . '/../pages-admin',
        'docs' => __DIR__ . '/../content/docs',
        'api' => __DIR__ . '/../pages-api',
    ],
],
```

Requests will check these folders in order. For example, `/admin/users` will look for:
1. `pages-admin/users.phtml` (via the `admin` folder mapping)
2. `pages/admin/users.phtml` (in the base `pages` directory)

### Template-Only Pages

Not every page needs code-behind logic. For simple content pages, create just the template:

```php
<!-- pages/about.phtml -->
<?php $this->layout('layouts::app', ['title' => 'About Us']); ?>

<h1>About Our Company</h1>
<p>Founded in 2024, we build amazing applications with Compose.</p>
```

The page will render with an empty data context (beyond what the layout provides).

### Working with Forms

Handle form submissions in your code-behind:

```php
// pages/contact.phtml.php
<?php
use Psr\Http\Message\ServerRequestInterface;

return static function (ServerRequestInterface $request): array {
    $data = ['success' => false, 'errors' => []];
    
    if ($request->getMethod() === 'POST') {
        $params = $request->getParsedBody();
        
        // Validate
        if (empty($params['email'])) {
            $data['errors']['email'] = 'Email is required';
        }
        
        // Process if valid
        if (empty($data['errors'])) {
            sendContactEmail($params);
            $data['success'] = true;
        }
    }
    
    return $data;
};
```

### Error Handling

If a page template doesn't exist, the Pages middleware passes control to the next middleware in the pipeline. Typically, this results in a 404 response from your error handling middleware.

You can customize 404 handling by:

1. Creating a `pages/404.phtml` template
2. Configuring error handling in your middleware stack
3. Returning custom responses from code-behind scripts

### Best Practices

**Keep code-behind focused**: Code-behind scripts should handle page-specific logic and data fetching. Move complex business logic to service classes.

**Use dependency injection**: Access services through the container rather than using global state:

```php
// pages/admin/dashboard.phtml.php
use Psr\Http\Message\ServerRequestInterface;
use Compose\Container\ContainerInterface;

return static function (ServerRequestInterface $request): array {
    $container = $request->getAttribute('container');
    $stats = $container->get(StatsService::class)->getDashboardStats();
    
    return ['stats' => $stats];
};
```

**Leverage layouts**: Define common HTML structure in layouts to keep page templates focused on content.

**Use URL parameters thoughtfully**: Design clean, readable URLs that map naturally to your page structure.

### Troubleshooting

**Page not rendering**: 
- Verify the file exists at the expected path
- Check file permissions
- Ensure `pages.dir` is configured correctly
- Look for typos in the filename

**Code-behind not executing**:
- Confirm the file has the `.phtml.php` extension
- Check that the file returns one of the three valid types (array, callable, or ResponseInterface)
- Verify PHP syntax is correct

**Parameters not passed to callable**:
- Ensure your code-behind returns a callable, not an array
- Check the callable signature matches the expected parameters
- Remember that the first parameter is always `ServerRequestInterface`

## Rendering Outside HTTP

Because the view engine only relies on Plates, you can reuse it in CLI commands, background jobs, or any other context by retrieving `ViewEngineInterface` from the container and calling `render()` directly. Pass `null` as the request when rendering outside HTTP.
