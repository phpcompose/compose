<?php
namespace Tests\Support;

use PHPUnit\Framework\TestCase;
use Compose\Container\ServiceContainer;
use Compose\Support\Factory\ErrorHandlerFactory;
use Compose\Support\Configuration;
use Compose\Template\RendererInterface;
use Laminas\Stratigility\Middleware\ErrorHandler;
use Laminas\Diactoros\ResponseFactory;

class ErrorHandlerFactoryTest extends TestCase
{
    public function testFactoryAttachesCallableListener()
    {
        $container = new ServiceContainer();
        // register a minimal view engine required by ErrorResponseGenerator
        $container->set(RendererInterface::class, new class implements RendererInterface {
            public function render(string $template, array $data = [], ?\Psr\Http\Message\ServerRequestInterface $request = null, ?string $layout = null): string { return ''; }
        });
        $listener = function($e, $req, $res) { return true; };
        $container->set(Configuration::class, new Configuration(['error_listeners' => [$listener]]));

        $handler = ErrorHandlerFactory::create($container, ErrorHandler::class);
        $this->assertInstanceOf(ErrorHandler::class, $handler);
    }

    public function testFactoryResolvesServiceIdListener()
    {
        $container = new ServiceContainer();
        $container->set(RendererInterface::class, new class implements RendererInterface {
            public function render(string $template, array $data = [], ?\Psr\Http\Message\ServerRequestInterface $request = null, ?string $layout = null): string { return ''; }
        });
        // register a listener service
        $listener = new class {
            public function __invoke($e, $req, $res) { return true; }
        };
        $container->set('my_listener', $listener);
        $container->set(Configuration::class, new Configuration(['error_listeners' => ['my_listener']]));

        $handler = ErrorHandlerFactory::create($container, ErrorHandler::class);
        $this->assertInstanceOf(ErrorHandler::class, $handler);
    }

    public function testInvalidListenerThrows()
    {
        $this->expectException(\InvalidArgumentException::class);
        $container = new ServiceContainer();
        $container->set(RendererInterface::class, new class implements RendererInterface {
            public function render(string $template, array $data = [], ?\Psr\Http\Message\ServerRequestInterface $request = null, ?string $layout = null): string { return ''; }
        });
        $container->set(Configuration::class, new Configuration(['error_listeners' => [123]]));
        ErrorHandlerFactory::create($container, ErrorHandler::class);
    }
}
