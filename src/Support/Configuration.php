<?php
namespace Compose\Support;


use Zend\Stdlib\ArrayUtils;

/**
 * Class Configuration
 * @package Compose\Support
 */
class Configuration extends \ArrayObject
{
    protected
        $env,

        $dir;

    /**
     * Configuration constructor.
     * @param string $dir
     * @param string $env
     */
    public function __construct(array $config = null)
    {
        parent::__construct($config);
    }


    /**
     * @param string $file
     * @throws \Exception
     */
    public function mergeFromFile(string $file) : void
    {
        if(!is_file($file)) {
            throw new  \Exception('File not found: ' . $file);
        }

        $config = include $file;
        if(!is_array($config)) {
            throw new \Exception('Config file does not return array.');
        }

        $this->merge($config);
    }

    /**
     * @param array $array
     */
    public function merge(array $array) : void
    {
        ArrayUtils::merge($this->getArrayCopy(), $array);
    }
}