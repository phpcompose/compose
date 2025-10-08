<?php
/**
 * Created by PhpStorm.
 * User: alaminahmed
 * Date: 2018-01-09
 * Time: 10:32 AM
 */

namespace Compose;


use Compose;
use Laminas\Stratigility\Middleware\ErrorHandler;

define('COMPOSE_DIR', dirname(dirname(__FILE__)));
define('COMPOSE_DIR_TEMPLATE', COMPOSE_DIR . '/templates');

class Config
{
    public function __invoke()
    {
        return [
            'app' => [
                'name' => 'php-compose app',
                'description' => 'A PHP-Compose based application',
            ],

            /**
             * debug
             *
             * Application wide debug state
             */
            'debug' => true,

            // error_listeners
            // Add error handler listeners. Each entry can be a service id (string)
            // resolving to a callable/object, or a direct callable/object.
            // Example: 'error_listeners' => [ MyApp\Error\LogListener::class ]
            'error_listeners' => [],

            /**
             * subscribers
             *
             * Plugin subscribers for listening and handling system-wide events
             */
            'subscribers' => [],

            /**
             * services
             *
             * Map of service identifiers to their definitions. Acceptable definitions are:
             *  - a string class name (instantiated lazily and shared)
             *  - a callable factory (receives the container and the service id)
             *  - an existing object instance (stored as-is)
             *  - null (alias to the service id itself)
             */
            'services' => [
                ErrorHandler::class => Compose\Support\Factory\ErrorHandlerFactory::class,
                \Psr\EventDispatcher\ListenerProviderInterface::class => Compose\Support\Factory\ListenerProviderFactory::class,
                \Psr\EventDispatcher\EventDispatcherInterface::class => Compose\Event\EventDispatcher::class,
                Compose\Http\Session\Session::class => Compose\Support\Factory\SessionFactory::class,
                Compose\Mvc\ViewEngineInterface::class => Compose\Support\Factory\ViewEngineFactory::class,
                Compose\Mvc\MvcMiddleware::class => Compose\Support\Factory\MvcMiddlewareFactory::class,
                Compose\Http\OutputBufferMiddleware::class => Compose\Http\OutputBufferMiddleware::class,
                Compose\Mvc\PagesMiddleware::class => Compose\Mvc\PagesMiddleware::class,
                Compose\Routing\RoutingMiddleware::class => Compose\Routing\RoutingMiddleware::class,
                Compose\Routing\DispatchMiddleware::class => Compose\Routing\DispatchMiddleware::class,
            ],

            /**
             * middleware
             *
             * Register application middleware here. The Starter will ksort() the
             * array and pipe middleware into the HTTP pipeline. Each entry may be:
             *  - a class name or service id (string) which will be resolved when
             *    the pipeline is executed,
             *  - a callable (will be wrapped by a CallableMiddlewareDecorator),
             *  - an object implementing Psr\Http\Server\MiddlewareInterface, or
             *  - an array of middleware (will be turned into a MiddlewarePipe).
             *
             * Recommended (priority keys control ordering):
             * 'middleware' => [
             *     10 => Compose\Http\OutputBufferMiddleware::class,
             *     20 => Compose\Http\BodyParsingMiddleware::class,
             *     90 => Compose\Mvc\MvcMiddleware::class,
             * ]
             *
             * You may also reference services registered in 'services' by id.
             */
            'middleware' => [
            ],


            /**
             * templates
             *
             * Configuration for the application's view engine. The ViewEngineFactory
             * consumes this array and supports the following keys (all optional):
             *
             *  - dir (string):      the base templates directory. Defaults to COMPOSE_DIR_TEMPLATE.
             *  - folders (array):   named folders mapped to absolute paths. Example: ['compose' => COMPOSE_DIR_TEMPLATE]
             *  - maps (array):      path map overrides for resolving view scripts. Keys are logical names -> paths.
             *  - layout (string):   optional default layout script (relative to resolved folders).
             *  - extension (string): template file extension (default: 'phtml').
             *  - helpers (array):   registry of view helpers. See notes below about registration.
             *
             * Notes about helpers:
             *  - Helpers must be defined under `templates['helpers']` (root-level
             *    `helpers` is no longer supported). This keeps view configuration
             *    colocated and unambiguous.
             *  - Helper entries may be provided in two ways:
             *      * numeric-keyed values (0-indexed or sequential) are treated as "extensions" and the
             *        value is passed directly to the HelperRegistry->extend(), allowing helper providers
             *        to register multiple helpers in a single class.
             *      * string-keyed entries map an alias to a helper definition (class name, service id or callable)
             *        and are registered via HelperRegistry->register(alias, definition).
             *  - Helper definitions will be resolved through the container using the ServiceResolver when
             *    helpers are later invoked.
             *
             * Example:
             * 'templates' => [
             *     'dir' => __DIR__ . '/../templates',
             *     'folders' => [ 'app' => __DIR__ . '/../templates/app' ],
             *     'maps' => [ 'error' => __DIR__ . '/../templates/error' ],
             *     'layout' => 'layout/main',
             *     'extension' => 'phtml',
             *     'helpers' => [
             *         // extend registry with a provider class
             *         0 => \App\View\HelperProvider::class,
             *         // register alias -> definition (class or service id)
             *         'format' => \Compose\Mvc\Helper\FormatterHelper::class,
             *     ],
             * ]
             */
            'templates' => [
                // map view scripts to view different paths/folders
                'maps' => [
                ],

                'folders' => [
                    'compose' => COMPOSE_DIR_TEMPLATE
                ],

                // 'layout' => path/to/default/layout.phtml

                'helpers' => [
                    Compose\Mvc\Helper\TagHelper::class,
                    Compose\Mvc\Helper\FormatterHelper::class,
                    'request' => Compose\Mvc\Helper\RequestHelper::class,
                ]
            ],

            /**
             * pages
             *
             * Pages are special dynamic pages that is processed before routing.
             */
            'pages' => [
                //'dir' =>  'path/to/pages',
            ],

            /**
             * routes
             *
             * Routes for request handling. The MvcMiddlewareFactory reads this
             * key and registers each entry with the RoutingMiddleware. The
             * expected shape is an associative array where the key is the path
             * (route pattern) and the value is the handler for that route.
             *
             * Handlers may be:
             *  - a class name / service id (resolved at dispatch time),
             *  - a callable, or
             *  - any value your dispatch layer understands.
             *
             * Example simple route map:
             * 'routes' => [
             *     '/about' => App\Controller\AboutController::class,
             *     '/ping'  => function ($req, $args) { return new \Laminas\Diactoros\Response(); },
             * ]
             *
             * If you need to declare method, name, or params, register routes
             * programmatically (the factory only maps path => handler via
             * Route::fromArray(['path' => ..., 'handler' => ...])).
             */
            'routes' => [
                // path => controller class
            ],
        ];
    }
}
