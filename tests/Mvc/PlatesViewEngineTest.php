<?php

declare(strict_types=1);

namespace Tests\Mvc;

use Compose\Mvc\PlatesViewEngine;
use Compose\Mvc\ViewEngineInterface;
use League\Plates\Engine;
use Laminas\Diactoros\ServerRequest;
use PHPUnit\Framework\TestCase;

final class PlatesViewEngineTest extends TestCase
{
    public function testRenderTemplate(): void
    {
        $engine = $this->createEngine();
        $viewEngine = new PlatesViewEngine($engine, null);
        $html = $viewEngine->render('hello', ['name' => 'Compose']);

        $this->assertStringContainsString('Hello Compose', $html);
    }

    public function testRenderWithLayout(): void
    {
        $engine = $this->createEngine();
        $viewEngine = new PlatesViewEngine($engine, 'layout');

        $html = $viewEngine->render('hello', ['name' => 'Compose']);

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
