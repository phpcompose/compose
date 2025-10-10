<?php
namespace Compose;


use Compose\Container\ServiceContainer;
use Compose\Http\Event\PipelineInitEvent;
use Compose\Http\Event\PipelineReadyEvent;
use Compose\Http\BodyParsingMiddleware;
use Compose\Http\OutputBufferMiddleware;
use Compose\Http\Pipeline;
use Compose\Pages\PagesMiddleware;
use Compose\Routing\DispatchMiddleware;
use Compose\Routing\Route;
use Compose\Routing\RoutingMiddleware;
use Compose\Support\Configuration;
use Compose\Support\Error\NotFoundMiddleware;
use Exception;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Laminas\Stratigility\Middleware\ErrorHandler;
use Laminas\Stratigility\Middleware\OriginalMessages;
use Psr\Container\NotFoundExceptionInterface;

/**
 * Class Starter
 * @package Compose
 */
class Starter
{
    /**
     * @param ContainerInterface $container
     * @param Pipeline $pipeline
     * @throws Exception
     */
    protected function onInit(ContainerInterface $container, Pipeline $pipeline)
    {
        $configuration = $container->get(Configuration::class);
        $pipeline->pipe($container->get(OutputBufferMiddleware::class));
        $pipeline->pipe(new OriginalMessages());
        $pipeline->pipe(new BodyParsingMiddleware());

        // add middleware to the stack
        $middleware = $configuration['middleware'] ?? [];
        ksort($middleware);
        $pipeline->pipeMany($middleware);

        // Pages middleware (filesystem-driven pages)
        $pagesMiddleware = $container->get(PagesMiddleware::class);
        if (method_exists($pagesMiddleware, 'setContainer')) {
            $pagesMiddleware->setContainer($container);
        }

        $pagesConfig = $configuration['pages'] ?? [];
        $pageDir = $pagesConfig['dir'] ?? null;
        if ($pageDir) {
            $pagesMiddleware->setDirectory($pageDir, $pagesConfig['namespace'] ?? null);
        }

        $pageFolders = $pagesConfig['folders'] ?? null;
        if ($pageFolders) {
            $pagesMiddleware->setFolders($pageFolders);
        }
        $pipeline->pipe($pagesMiddleware);

        // Routing middleware
        $routing = $container->get(RoutingMiddleware::class);
        if (method_exists($routing, 'setContainer')) {
            $routing->setContainer($container);
        }

        $routes = $configuration['routes'] ?? [];
        foreach ($routes as $path => $handler) {
            $routing->route(Route::fromArray([
                'path' => $path,
                'handler' => $handler,
            ]));
        }
        $pipeline->pipe($routing);

        // Dispatch middleware
        $dispatch = $container->get(DispatchMiddleware::class);
        if (method_exists($dispatch, 'setContainer')) {
            $dispatch->setContainer($container);
        }
        $pipeline->pipe($dispatch);
    }


    /**
     * @param Configuration $configuration
     * @return Pipeline
     * @throws Exception
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function init(Configuration $configuration, ?callable $callback = null): Pipeline
    {
        // application http pipeline setup
        $container = $this->createContainer($configuration);
        $pipeline = $this->createPipeline($container);

        // register the main error handler
        $pipeline->pipe($container->get(ErrorHandler::class));

        /** @var EventDispatcherInterface $dispatcher */
        $dispatcher = $container->get(EventDispatcherInterface::class);
        $dispatcher->dispatch(new PipelineInitEvent($container));

        $this->onInit($container, $pipeline);
        if ($callback) {
            $callback($container, $pipeline);
        }

        $dispatcher->dispatch(new PipelineReadyEvent($container));

        // now final handler/not found handler
        $pipeline->pipe($container->get(NotFoundMiddleware::class));
        return $pipeline;
    }

    /**
     * @param Configuration $configuration
     * @return ContainerInterface
     */
    protected function createContainer(Configuration $configuration): ContainerInterface
    {
        $container = new ServiceContainer();

        $container->set(Configuration::class, $configuration);
        $container->setMany($configuration['services'] ?? []);

        return $container;
    }

    /**
     * @param ContainerInterface $container
     * @return Pipeline
     * @throws Exception
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    protected function createPipeline(ContainerInterface $container): Pipeline
    {
        $pipeline = new Pipeline();
        $pipeline->setContainer($container);

        return $pipeline;
    }

    /**
     * @param array $config
     * @throws Exception
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    static public function start(array $config, ?callable $callback = null)
    {
        $configuration = new Configuration($config);
        $starter = new static();
        $pipeline = $starter->init($configuration, $callback);
        $pipeline->listen();
    }

}
