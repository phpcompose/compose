<?php
/**
 * Created by PhpStorm.
 * User: alaminahmed
 * Date: 2016-11-02
 * Time: 5:21 PM
 */

namespace Compose\Common;


use Zend\Stdlib\ArrayUtils;
use Zend\Stdlib\Glob;

class Configuration extends \ArrayObject
{
    protected
        /**
         * @var array directories managed by the config manager
         */
        $dirs = [];
    /**
     * ConfigManager constructor.
     */
    public function __construct()
    {
        parent::__construct([], \ArrayObject::ARRAY_AS_PROPS);
    }

    /**
     * Load config files using glob functionality
     *
     * @param string $pattern
     */
    public function glob(string $pattern)
    {
        $files = Glob::glob($pattern, Glob::GLOB_BRACE);
        $this->loadFiles($files);
    }

    /**
     * Load given file
     *
     * @param string $filename
     * @throws \Exception
     */
    public function loadFile(string $filename)
    {
        if(!is_file($filename)) {
            throw new \Exception("Config file not found: {$filename}");
        }

        $this->merge(include $filename);
    }

    /**
     * Load given files
     *
     * @param array $files
     */
    public function loadFiles(array $files)
    {
        foreach($files as $file) {
            $this->loadFile($file);
        }
    }


    /**
     * @param array ...$arrays
     */
    public function merge(...$arrays)
    {
        if(!count($arrays)) return;

        $config = $this->getArrayCopy();
        foreach($arrays as $array) {
            $config = ArrayUtils::merge($config, $array);
        }

        $this->exchangeArray($config);
    }

    public function __destruct()
    {
    }
}