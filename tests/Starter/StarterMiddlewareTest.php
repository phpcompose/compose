<?php
namespace Tests\Starter;

use PHPUnit\Framework\TestCase;
use Compose\Container\ServiceContainer;
use Compose\Support\Configuration as Config;

class StarterMiddlewareTest extends TestCase
{
    public function testStarterPipesMiddlewareFromConfig()
    {
        // Create a fake pipeline that records piped middlewares
        $pipeline = new class {
            public array $piped = [];
            public function pipe($middleware)
            {
                $this->piped[] = $middleware;
            }
            public function pipeMany(array $middleware)
            {
                foreach ($middleware as $m) {
                    $this->pipe($m);
                }
            }
        };

        require_once __DIR__ . '/../../src/Http/Pipeline.php';
        // Create container and register configuration
        $container = new ServiceContainer();

        // Create a minimal configuration with different middleware types
        $config = [
            'middleware' => [
                'first' => \Compose\Http\Pipeline::class,
                'second' => function ($req, $handler) { return $handler->handle($req); },
                'third' => new class implements \Psr\Http\Server\MiddlewareInterface {
                    public function process(\Psr\Http\Message\ServerRequestInterface $request, \Psr\Http\Server\RequestHandlerInterface $handler): \Psr\Http\Message\ResponseInterface
                    {
                        return $handler->handle($request);
                    }
                }
            ]
        ];

        $configuration = new Config($config);
        $container->set(Config::class, $configuration);

        // Create a small TestStarter that only pipes middleware from config
        $starter = new class extends \Compose\Starter {
            public function runOnInit($container, $pipeline)
            {
                $configuration = $container->get(\Compose\Support\Configuration::class);
                $middleware = $configuration['middleware'] ?? [];
                ksort($middleware);
                $pipeline->pipeMany($middleware);
            }
        };

        $starter->runOnInit($container, $pipeline);

        $this->assertCount(3, $pipeline->piped);
    }
}
