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

            /**
             * error_listeners
             *
             * Add error handler's listeners
             * Must be callable __invoke($exception, $request, $response)
             */
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
                Compose\Event\EventDispatcherInterface::class => Compose\Support\Factory\EventDispatcherFactory::class,
                Compose\Http\Session\Session::class => Compose\Support\Factory\SessionFactory::class,
                Compose\Mvc\ViewEngineInterface::class => Compose\Support\Factory\ComposeViewEngineFactory::class,
                Compose\Mvc\MvcMiddleware::class => Compose\Support\Factory\MvcMiddlewareFactory::class,
                Compose\Http\OutputBufferMiddleware::class => Compose\Http\OutputBufferMiddleware::class,
                Compose\Mvc\PagesMiddleware::class => Compose\Mvc\PagesMiddleware::class,
                Compose\Routing\RoutingMiddleware::class => Compose\Routing\RoutingMiddleware::class,
                Compose\Routing\DispatchMiddleware::class => Compose\Routing\DispatchMiddleware::class,
            ],

            /**
             * middleware
             *
             */
            'middleware' => [
            ],

            'pipeline' => [
                'init' => [],
                'ready' => [],
                'routing' => [],
                'final' => []
            ],

            /**
             * templates
             */
            'templates' => [
                // map view scripts to view different paths/folders
                'maps' => [
                ],

                'folders' => [
                    'compose' => COMPOSE_DIR_TEMPLATE
                ],

                // 'layout' => path/to/default/layout.phtml
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
             * Routes for request handling, ie. controllers etc.
             */
            'routes' => [
                // path => controller class
            ],

            /**
             * helpers
             *
             * Map helper aliases to callables registered with the view engine.
             */
            'helpers' => [],
        ];
    }
}
