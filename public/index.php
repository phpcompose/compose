<?php
use Psr\Http\Message\ServerRequestInterface;
use Interop\Http\Middleware\DelegateInterface;

// Delegate static file requests back to the PHP built-in webserver
if (php_sapi_name() === 'cli-server'
    && is_file(__DIR__ . parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH))
) {
    return false;
}

chdir(dirname(__DIR__));
require 'vendor/autoload.php';


// get all application configurations
$config = require 'config/config.php';

/** @var \Interop\Container\ContainerInterface $container */
$container = require 'config/container.php';
$container = \Compose\Support\ContainerFactory::createFromConfig($config, $container);

// setup the application pipeline
$app = new \Compose\System\Http\Pipeline($container);
$app->pipe($container->get(\Zend\Stratigility\Middleware\ErrorHandler::class));


// setup the front-controller for handling requests
$web = new \Compose\Mvc\FrontController($container);
$app->pipe($web);
$web->route('/hey', \App\Test\Action\HelloAction::class);
$app->pipe('/err', function(ServerRequestInterface $request, DelegateInterface $delegate) {
    throw new Exception('Testing exception');
});


// create and start server for listening incoming requests
$server = \Zend\Diactoros\Server::createServerFromRequest(
    $app,
    \Zend\Diactoros\ServerRequestFactory::fromGlobals()
);
$server->listen($container->get(\Compose\Support\Error\NotFoundMiddleware::class));