<?php

declare(strict_types=1);

namespace Tests\Template;

use Compose\Bridge\Plates\PlatesViewEngine;
use League\Plates\Engine;
use PHPUnit\Framework\TestCase;

final class PlatesRendererTest extends TestCase
{
    public function testRenderTemplate(): void
    {
        $engine = $this->createEngine();
        $renderer = new PlatesViewEngine($engine, null);
        $html = $renderer->render('hello', ['name' => 'Compose']);

        $this->assertStringContainsString('Hello Compose', $html);
    }

    public function testRenderWithLayout(): void
    {
        $engine = $this->createEngine();
        $renderer = new PlatesViewEngine($engine, 'layout');

        $html = $renderer->render('hello', ['name' => 'Compose']);

        $this->assertStringContainsString('<main>Hello Compose</main>', $html);
    }

    private function createEngine(): Engine
    {
        $dir = sys_get_temp_dir() . '/plates-tests-' . uniqid();
        mkdir($dir, 0777, true);

        file_put_contents($dir . '/hello.phtml', 'Hello <?= $name ?>');
        file_put_contents($dir . '/layout.phtml', "<html><body><main><?= \$content ?></main></body></html>");

        $engine = new Engine($dir);
        $engine->setFileExtension('phtml');

        return $engine;
    }
}
