<?php
use Zend\Stratigility\MiddlewarePipe;
use Zend\Stratigility\NoopFinalHandler;
use Zend\Diactoros\Server;
use Interop\Http\ServerMiddleware\DelegateInterface;
use Zend\Diactoros\Response\TextResponse;

// Delegate static file requests back to the PHP built-in webserver
if (php_sapi_name() === 'cli-server'
    && is_file(__DIR__ . parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH))
) {
    return false;
}

chdir(dirname(__DIR__));
$loader = require 'vendor/autoload.php';
$loader->addPsr4('App\\',__DIR__ . '/../src/App');


$container = \Compose\Support\ContainerFactory::createFromConfig([]);

$app    = new MiddlewarePipe();
$app->setResponsePrototype(new \Zend\Diactoros\Response());

$app->pipe(new \Zend\Stratigility\Middleware\OriginalMessages());
$app->pipe(new \Zend\Stratigility\Middleware\ErrorHandler(
    new \Zend\Diactoros\Response(),
    new \Zend\Stratigility\Middleware\ErrorResponseGenerator(true)
));

$fc = new \Compose\Mvc\FrontController($container);
$fc->setResponsePrototype(new \Zend\Diactoros\Response());
$app->pipe($fc);
$fc->route('/test', \App\Test\Action\HelloAction::class);

$app->pipe('/api', function ($request, DelegateInterface $delegate) {
    return new \Zend\Diactoros\Response\JsonResponse([
        'hello' => 'world'
    ]);

});


$server = Server::createServer($app,
    $_SERVER,
    $_GET,
    $_POST,
    $_COOKIE,
    $_FILES
);
$server->listen(new NoopFinalHandler());
?>

