<?php

declare(strict_types=1);

namespace Tests\Template;

use Compose\Container\ServiceContainer;
use Compose\Container\ServiceResolver;
use Compose\Template\Helper\HelperRegistry;
use Compose\Template\Helper\TagHelper;
use Compose\Template\TemplateRenderer;
use Laminas\Diactoros\ServerRequest;
use PHPUnit\Framework\TestCase;

final class TemplateRendererTest extends TestCase
{
    public function testRenderWithLayout(): void
    {
        $dir = $this->createTemplates([
            'home/index.phtml' => "<?php \$this->layout = 'layout'; ?>Hello <?= \$name ?>",
            'layout.phtml' => "<html><body><main><?= \$this->get('content') ?></main></body></html>",
        ]);

        $engine = $this->createRenderer(['dir' => $dir, 'layout' => 'layout']);

        $html = $engine->render('home/index', ['name' => 'Compose'], new ServerRequest());

        $this->assertStringContainsString('<main>Hello Compose</main>', $html);
    }

    public function testSectionsAndSharedDataAreAvailableInLayout(): void
    {
        $dir = $this->createTemplates([
            'home/index.phtml' => <<<'PHP'
<?php $this->layout = 'layout'; ?>
<?php $this->set('menu', ['Dashboard']); ?>
<?php $this->start('sidebar'); ?>Sidebar<?php $this->end(); ?>
<p>Main Area</p>
PHP,
            'layout.phtml' => <<<'PHP'
<html><body>
<nav><?= implode(',', (array) $this->get('menu', [])) ?></nav>
<aside><?= trim($this->get('sidebar', '')) ?></aside>
<main><?= $this->get('content') ?></main>
</body></html>
PHP,
        ]);

        $engine = $this->createRenderer(['dir' => $dir]);

        $html = $engine->render('home/index', [], new ServerRequest());

        $this->assertStringContainsString('<nav>Dashboard</nav>', $html);
        $this->assertStringContainsString('<aside>Sidebar</aside>', $html);
        $this->assertStringContainsString('<main><p>Main Area</p></main>', $html);
    }

    public function testNumericHelperRegistrationExtendsMethods(): void
    {
        $dir = $this->createTemplates([
            'home/index.phtml' => <<<'PHP'
<?php $this->layout = 'layout'; ?>
<?php echo $this->open('section', ['class' => 'hero']); ?>
    <h2>Hello</h2>
<?php echo $this->close('section'); ?>
PHP,
            'layout.phtml' => <<<'PHP'
<html><body><?= $this->get('content') ?></body></html>
PHP,
        ]);

        $engine = $this->createRenderer([
            'dir' => $dir,
            'layout' => 'layout',
            'helpers' => [TagHelper::class],
        ]);

        $html = $engine->render('home/index', [], new ServerRequest());

        $this->assertStringContainsString('<section class="hero">', $html);
        $this->assertStringContainsString('</section>', $html);
    }

    public function testTemplateLookup(): void
    {
        $dir = $this->createTemplates([
            'about.phtml' => 'About',
        ]);

        $engine = $this->createRenderer(['dir' => $dir]);

        $this->assertTrue($engine->hasTemplate('about'));
        $this->assertNotNull($engine->resolvePath('about'));
        $this->assertFalse($engine->hasTemplate('missing'));
    }

    private function createRenderer(array $templates): TemplateRenderer
    {
        $container = new ServiceContainer();
        $resolver = new ServiceResolver($container);
        $registry = new HelperRegistry($resolver);

        $helpers = $templates['helpers'] ?? [];
        foreach ($helpers as $alias => $definition) {
            if (is_int($alias)) {
                $registry->extend($definition);
            } else {
                $registry->register($alias, $definition);
            }
        }

        return new TemplateRenderer(array_merge([
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
