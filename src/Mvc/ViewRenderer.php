<?php
namespace Compose\Mvc;


use Compose\Mvc\Helper\HelperRegistry;
use Exception;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class ViewRenderer
 *
 * View Renderer engine
 *
 * Support view rendering with layout
 * Also supports helpers
 * @package Compose\Mvc
 */
class ViewRenderer implements ViewRendererInterface
{
    protected
        $_defaultLayout = null,

        /**
         * @var
         */
        $_helperRegistry,

        /**
         * @var array
         */
        $_folders = [],

        $_maps = [];


    const
        VIEW_EXT = 'phtml',
        CODE_EXT = 'php';


    /**
     * ViewRenderer constructor.
     * @param array $templates
     * @param HelperRegistry $helpers
     */
    public function __construct(array $templates, HelperRegistry $helpers)
    {
        $this->_defaultLayout = $templates['layout'] ?? null;
        $this->_folders = $templates['folders'] ?? [];
        $this->_maps = $templates['maps'] ?? [];


        $this->setHelperRegistry($helpers);
    }

    /**
     * @param HelperRegistry $helpers
     */
    public function setHelperRegistry(HelperRegistry $helpers)
    {
        $this->_helperRegistry = $helpers;
    }

    /**
     * @return HelperRegistry
     */
    public function getHelperRegistry() : HelperRegistry
    {
        return $this->_helperRegistry;
    }

    /**
     * @param string $scriptName
     * @return null|string
     */
    public function resolve(string $scriptName) : ?string
    {
        // first check if script can be resolved
        $file = realpath($scriptName);
        if($file) return $file;

        // check if script mapping available
        $scriptName = $this->_maps[$scriptName] ?? $scriptName;

        // now check if script name exists in the templates
        $folder = $script = $dir = null;
        $parts = explode('::', $scriptName);
        if(count($parts) == 2) {
            $folder = $parts[0];
            $script = $parts[1];
            $dir = $this->_folders[$folder] ?? null;
            $script = rtrim($dir, '/') . '/' . $script;
        } else if(count($parts) == 1) {
            $script = $parts[0];
        } else {
            // this is a problem, ie more then on set of ::
            throw new \LogicException('Malformed view script name: ' . $scriptName);
        }

        $fileInfo = pathinfo($script);
        if (!isset($fileInfo['extension'])) {
            $script = $script . '.' . self::VIEW_EXT;
        }

        if(file_exists($script)) {
            return $script;
        }

        return null; // unable to resolve
    }

    /**
     * @param View $view
     * @param ServerRequestInterface|null $request
     * @return string
     * @throws Exception
     */
    public function render(View $view, ServerRequestInterface $request = null) : string
    {
        $registry = $this->getHelperRegistry();
        $registry->setContext($view, $request);

        $view->setHelperRegistry($registry);

        try {
            $view->layout = $this->_defaultLayout;

            $content = $this->renderScript($view->getScript(), $view->getArrayCopy(), $view);

            if($view->layout) {
                $view->content($content);
                $content = $this->renderScript($view->layout, [], $view);
            }

            return $content;
        } finally {
            $registry->setContext(null, null);
        }
    }

    /**
     * @param string $script
     * @param array|null $locals
     * @param null $bind
     * @return string
     * @throws Exception
     * @internal param $script
     */
    public function renderScript(string $script, array $locals = null, $bind = null) : string
    {
        $filename = $this->resolve($script);
        if(!$filename) {
            throw new Exception("Unable to resolve view script: " . $script);
        }

        // render the script within closure with given $bind
        $closure = \Closure::bind(function(string $__filename, array $__data) {
            ob_start();
            extract($__data);

            include $__filename;
            return ob_get_clean();
        }, $bind);

        return $closure($filename, $locals);
    }
}
