<?php
namespace Compose;


use Compose\Container\ServiceContainer;
use Compose\Container\ServiceResolver;
use Compose\Event\EventDispatcherInterface;
use Compose\Event\Message;
use Compose\Http\BodyParsingMiddleware;
use Compose\Mvc\MvcMiddleware;
use Compose\Http\Pipeline;
use Compose\Support\Configuration;
use Compose\Support\Error\NotFoundMiddleware;
use Psr\Container\ContainerInterface;
use Zend\ServiceManager\ServiceManager;
use Zend\Stratigility\Middleware\ErrorHandler;
use Zend\Stratigility\Middleware\OriginalMessages;

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
     * @param ServiceContainer $container
     * @param Pipeline $pipeline
     * @throws \Exception
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
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
     * @throws \Exception
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(Configuration $configuration) : Pipeline
    {
        // order 1: first initial application setup and required middleware
        // application http pipeline setup
        $container = $this->createContainer($configuration);
        $pipeline = $this->createPipeline($container);

        /** @var EventDispatcherInterface $notifier */
        $notifier = $container->get(EventDispatcherInterface::class);
        $notifier->notify(new Message(self::EVENT_INIT, ['container' => $container], $this));

        $pipeline->pipe($container->get(ErrorHandler::class));

        $this->onInit($container, $pipeline);

        $notifier->notify(new Message(self::EVENT_READY, ['container' => $container], $this));

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
//        $container = new ServiceContainer();
//        $container->setMany($configuration['services'] ?? []);
//        $container->set(Configuration::class, $configuration);

        $dependencies = $configuration['dependencies'] ?? [];
        $container = new ServiceManager($dependencies);

        $services = [
            'factories' => $configuration['services']
            ];
        $container->configure($services);

        $container->setService(Configuration::class, $configuration);
        $container->setService(ServiceResolver::class, new ServiceResolver($container));

        return $container;
    }

    /**
     * @param ContainerInterface $container
     * @return Pipeline
     * @throws \Exception
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    protected function createPipeline(ContainerInterface $container) : Pipeline
    {
        $pipeline = new Pipeline();
        $pipeline->setContainer($container);

        return $pipeline;
    }

    /**
     * @param array $config
     * @throws \Exception
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    static public function start(array $config)
    {
        $configuration = new Configuration($config);
        $starter = new static();
        $pipeline = $starter($configuration);
        $pipeline->listen();
    }
}