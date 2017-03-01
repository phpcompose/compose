<?php
/**
 * Created by PhpStorm.
 * User: Alamin
 */

namespace Compose\Adapter\League;

use Compose\Mvc\ViewRendererInterface;
use Compose\System\ConfigurationInterface;
use Compose\System\Container\ServiceAwareInterface;
use League\Plates\Engine;


/**
 * Class PlatesViewRenderer
 * @package Compose\Adapter\League
 */
class PlatesViewRenderer implements ViewRendererInterface, ServiceAwareInterface
{
    const
        DEFAULT_EXTENSION = 'phtml';

    protected
        $templateDirectory,
        $extension,

        /**
         * @var Engine
         */
        $engine = null;


    /**
     * PlatesViewRenderer constructor.
     * @param ConfigurationInterface $config
     */
    public function __construct(ConfigurationInterface $config)
    {
        $templates = $config['templates'] ?? [];
        $ext = $templates['extension'] ?? self::DEFAULT_EXTENSION;
        $engine = new Engine('./', $ext);
        $this->engine = $engine;

        if(($paths = $templates['paths'] ?? null)) {
            foreach($paths as $namespace => $dir) {
                $this->addPath($dir, $namespace);
            }
        }

        if(!$this->engine->exists('compose')) {
            $this->addPath(COMPOSE_DIR_TEMPLATE, 'compose');
        }
    }

    /**
     * Lazy load Plates engine
     *
     * @return Engine
     */
    public function getEngine()
    {
        return $this->engine;
    }

    /**
     * @param $namespace
     * @param $dir
     * @return mixed|void
     */
    public function addPath($dir, $namespace)
    {
        $dirs = (array) $dir;
        foreach($dirs as $dir) {
            $this->engine->addFolder($namespace, $dir);
        }
    }

    /**
     * @todo supports direct page rendering
     * @param string $script
     * @param array $data
     * @return mixed|string
     */
    public function render(string $script, array $data = []) : string
    {
        /** @var Engine $engine */
        $engine = $this->getEngine();

        return $engine->render($script, $data);
    }
}