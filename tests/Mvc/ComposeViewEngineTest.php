<?php

declare(strict_types=1);

namespace Tests\Mvc;

use Compose\Container\ServiceContainer;
use Compose\Container\ServiceResolver;
use Compose\Mvc\ComposeViewEngine;
use Compose\Mvc\Helper\HelperRegistry;
use Compose\Mvc\Helper\LayoutHelper;
use Compose\Mvc\ViewEngineInterface;
use Laminas\Diactoros\ServerRequest;
use PHPUnit\Framework\TestCase;

final class ComposeViewEngineTest extends TestCase
{
    public function testRenderWithLayout(): void
    {
        $dir = $this->createTemplates([
            'home/index.phtml' => 'Hello <?= $name ?>',
            'layout.phtml' => '<html><body><main><?= $this->section(\'content\') ?></main></body></html>',
        ]);

        $engine = $this->createEngine(['dir' => $dir, 'layout' => 'layout']);

        $html = $engine->render('home/index', ['name' => 'Compose'], new ServerRequest());

        $this->assertStringContainsString('<main>Hello Compose</main>', $html);
    }

    public function testTemplateLookup(): void
    {
        $dir = $this->createTemplates([
            'about.phtml' => 'About',
        ]);

        $engine = $this->createEngine(['dir' => $dir]);

        $this->assertTrue($engine->hasTemplate('about'));
        $this->assertNotNull($engine->resolvePath('about'));
        $this->assertFalse($engine->hasTemplate('missing'));
    }

    private function createEngine(array $templates): ViewEngineInterface
    {
        $container = new ServiceContainer();
        $resolver = new ServiceResolver($container);
        $registry = new HelperRegistry($resolver);
        $registry->register('layout', LayoutHelper::class);
        $registry->registerMethodAlias('content', 'layout', 'content');
        $registry->registerMethodAlias('section', 'layout', 'section');

        return new ComposeViewEngine(array_merge([
            'dir' => $templates['dir'] ?? null,
            'folders' => $templates['folders'] ?? [],
            'maps' => $templates['maps'] ?? [],
            'layout' => $templates['layout'] ?? null,
            'extension' => $templates['extension'] ?? 'phtml',
        ], $templates), $registry);
    }

    private function createTemplates(array $files): string
    {
        $dir = sys_get_temp_dir() . '/compose-view-' . uniqid();
        mkdir($dir, 0777, true);

        foreach ($files as $name => $contents) {
            $path = $dir . '/' . $name;
            $folder = dirname($path);
            if (!is_dir($folder)) {
                mkdir($folder, 0777, true);
            }
            file_put_contents($path, $contents);
        }

        return $dir;
    }
}
