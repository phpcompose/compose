<?php
namespace Compose\App;

use Compose\Container\ServiceFactoryInterface;
use Compose\Support\Configuration;
use League\Plates\Engine;
use Psr\Container\ContainerInterface;

class TemplateRenderer implements ServiceFactoryInterface
{
    protected Engine $engine;

    protected string $templateExt = 'phtml';

    /**
     * 
     */
    public function __construct(string $dir = null, array $folders = null)
    {
        // $templates = $config['templates'] ?? [];
        // $folders = $templates['folders'] ?? [];
        $plates = new Engine($dir);
        $plates->setFileExtension($this->templateExt);
        foreach($folders as $name => $dir) {
            $plates->addFolder($name, $dir);
        }

        $this->engine = $plates;
    }

    public function getEngine() : Engine
    {
        return $this->engine;
    }

    /**
     * Container factory function for creating the instance
     */
    public static function create(ContainerInterface $container, string $name)
    {
        $config = $container->get(Configuration::class);
        $templates = $config['templates'] ?? [];
        $dir = $templates['dir'] ?? null;
        $folders = $templates['folders'] ?? [];
        $instance = new static($dir, $folders);
        return $instance;
    }

    public function render(string $name, array $data = null) : string
    {
        return $this->engine->render($name, $data);
    }
}