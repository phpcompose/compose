<?php
use Psr\Http\Message\ServerRequestInterface;
use Interop\Http\Middleware\DelegateInterface;
use Psr\Http\Message\ResponseInterface;

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


$app = new \Zend\Stratigility\MiddlewarePipe();

// setup error handlings
$app->raiseThrowables();
$app->pipe(new \Zend\Stratigility\Middleware\ErrorHandler(new \Zend\Diactoros\Response(), new \Zend\Stratigility\Middleware\ErrorResponseGenerator(true)));


$frontController = new \Compose\Express\RequestHandler($container);
$frontController->route('/app', \App\Test\Action\HelloAction::class);
$app->pipe($frontController);





$app->pipe('/hello', function(ServerRequestInterface $req, DelegateInterface $del) {
    return new \Zend\Diactoros\Response\HtmlResponse('<h1>Hello World</h1>');
});

$app->pipe('/err', function(ServerRequestInterface $request, DelegateInterface $delegate) {
    throw new Exception('Testing exception');
});



$app->pipe(new \Zend\Stratigility\Middleware\NotFoundHandler(new \Zend\Diactoros\Response()));

//
$server = \Zend\Diactoros\Server::createServer(
    $app,
    $_SERVER,
    $_GET,
    $_POST,
    $_COOKIE,
    $_FILES
);
$server->listen(new \Zend\Stratigility\NoopFinalHandler());