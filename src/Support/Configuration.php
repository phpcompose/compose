<?php
namespace Compose\Support;


use Exception;
use Laminas\Stdlib\ArrayUtils;

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
     * @param array|null $config
     */
    public function __construct(array $config = null)
    {
        parent::__construct($config);
    }


    /**
     * @param string $file
     * @throws Exception
     */
    public function mergeFromFile(string $file) : void
    {
        if(!is_file($file)) {
            throw new  Exception('File not found: ' . $file);
        }

        $config = include $file;
        if(!is_array($config)) {
            throw new Exception('Config file does not return array.');
        }

        $this->merge($config);
    }

    /**
     * @param string $keyPath
     * @param null $default
     * @param string $keyPathSeparator
     * @return Configuration|mixed|null
     */
    public function getNestedValue(string $keyPath, $default = null, string $keyPathSeparator = '.')
    {
        $keys = explode($keyPathSeparator, $keyPath);

        if (empty($keys)) {
            return $default;
        }

        $var = $this;
        foreach ($keys as $key) {
            if (!isset($var[$key])) {
                return $default;
            }
            $var = $var[$key];
        }

        return $var;
    }

    /**
     * @param array $array
     */
    public function merge(array $array) : void
    {
        ArrayUtils::merge($this->getArrayCopy(), $array);
    }
}