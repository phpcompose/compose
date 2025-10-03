<?php

declare(strict_types=1);

namespace Tests\Mvc;

use Compose\Container\ServiceContainer;
use Compose\Mvc\Helper\HelperRegistry;
use Compose\Mvc\Helper\LayoutHelper;
use Compose\Mvc\View;
use Laminas\Diactoros\ServerRequest;
use PHPUnit\Framework\TestCase;

final class HelperRegistryTest extends TestCase
{
    private HelperRegistry $registry;

    protected function setUp(): void
    {
        $container = new ServiceContainer();
        $resolver = $container->getResolver();

        $this->registry = new HelperRegistry($resolver);
        $this->registry->register('layout', LayoutHelper::class);
        $this->registry->register('sayhi', function(HelperRegistry $helpers) {
            return 'hi';
        });
        $this->registry->registerMethodAlias('content', 'layout', 'content');
    }

    public function testInvokableHelperIsResolved(): void
    {
        $view = new View('foo', []);
        $request = new ServerRequest();

        $this->registry->setContext($view, $request);

        $helper = $this->registry->get('layout');
        $this->assertInstanceOf(LayoutHelper::class, $helper);

        $helper->content('body');
        $this->assertSame('body', $helper->content());
    }

    public function testClosureHelperReceivesRegistry(): void
    {
        $this->assertSame('hi', $this->registry->call('sayhi'));
    }

    public function testMethodAliasDelegatesToHelper(): void
    {
        $view = new View('foo', []);
        $request = new ServerRequest();
        $this->registry->setContext($view, $request);

        $this->registry->call('content', 'body');
        $layout = $this->registry->get('layout');
        $this->assertSame('body', $layout->content());
    }
}
