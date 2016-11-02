<?php

// Delegate static file requests back to the PHP built-in webserver
if (php_sapi_name() === 'cli-server'
    && is_file(__DIR__ . parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH))
) {
    return false;
}

chdir(dirname(__DIR__));
require 'vendor/autoload.php';

/** @var \Interop\Container\ContainerInterface $container */
$container = require 'config/container.php';

/** @var \Zend\Expressive\Application $app */
$app = $container->get(\Zend\Expressive\Application::class);

/** @var \Compose\Express\RequestHandler $express */
$express = \Compose\Express\RequestHandlerFactory::create($container);

$app->pipe($express);

/** @var \Zend\Diactoros\Server $server */
$server = \Zend\Diactoros\Server::createServerFromRequest(
    $app,
    \Zend\Diactoros\ServerRequestFactory::fromGlobals()
);

$server->listen();