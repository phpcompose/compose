<?php
namespace Compose;


use Compose\Container\ServiceContainer;
use Compose\Event\EventDispatcherInterface;
use Compose\Event\Message;
use Compose\Http\BodyParsingMiddleware;
use Compose\Mvc\MvcMiddleware;
use Compose\Http\Pipeline;
use Compose\Support\Configuration;
use Compose\Support\Error\NotFoundMiddleware;
use Exception;
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
    const
        EVENT_INIT = 'starter.init',
        EVENT_READY = 'starter.ready';

    /**
     * @param ContainerInterface $container
     * @param Pipeline $pipeline
     * @throws Exception
     */
    protected function onInit(ContainerInterface $container, Pipeline $pipeline)
    {
        $configuration = $container->get(Configuration::class);
        $pipeline->pipe(new OriginalMessages());
        $pipeline->pipe(new BodyParsingMiddleware());

        // add middleware to the stack
        $middleware = $configuration['middleware'] ?? [];
        ksort($middleware);
        $pipeline->pipeMany($middleware);

        $pipeline->pipe(MvcMiddleware::class);
    }


    /**
     * @param Configuration $configuration
     * @return Pipeline
     * @throws Exception
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __invoke(Configuration $configuration) : Pipeline
    {
        // application http pipeline setup
        $container = $this->createContainer($configuration);
        $pipeline = $this->createPipeline($container);

        // register the main error handler
        $pipeline->pipe($container->get(ErrorHandler::class)); 

        /** @var EventDispatcherInterface $notifier */
        $notifier = $container->get(EventDispatcherInterface::class);
        $notifier->dispatch(new Message(self::EVENT_INIT, ['container' => $container], $this));

        $this->onInit($container, $pipeline);

        $notifier->dispatch(new Message(self::EVENT_READY, ['container' => $container], $this));

        // now final handler/not found handler
        $pipeline->pipe($container->get(NotFoundMiddleware::class));
        return $pipeline;
    }

    /**
     * @param Configuration $configuration
     * @return ContainerInterface
     */
    protected function createContainer(Configuration $configuration) : ContainerInterface
    {
        $container = new ServiceContainer();
        $container->setMany($configuration['services'] ?? []);
        $container->set(Configuration::class, $configuration);

        return $container;
    }

    /**
     * @param ContainerInterface $container
     * @return Pipeline
     * @throws Exception
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    protected function createPipeline(ContainerInterface $container) : Pipeline
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
    static public function start(array $config)
    {
        $configuration = new Configuration($config);
        $starter = new static();
        $pipeline = $starter($configuration);
        $pipeline->listen();
    }
}