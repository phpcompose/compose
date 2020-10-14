<?php

namespace Compose\Mvc;


use Compose\Mvc\Helper\HelperRegistry;

/**
 * Class View
 *
 * Represents a single View - a HTML document.
 *
 * @package Compose\Mvc
 */
class View extends \ArrayObject
{
    public
        /**
         * Layout script requested for the view
         * @var null
         */
        $layout = null,

        /**
         * @var null
         */
        $title = null;

    protected
        /**
         * @var HelperRegistry
         */
        $_helpers,

        /**
         * Stores rendered content (without layout)
         * @var null
         */
        $_content = null,


        /**
         * @var null|string
         */
        $_script = null;


    /**
     * View constructor.
     * @param string $script
     * @param array|null $data
     */
    public function __construct(string $script, array $data = null)
    {
        parent::__construct($data ?? []);
        $this->_script = $script;
    }

    /**
     * @param HelperRegistry $helpers
     */
    public function setHelperRegistry(HelperRegistry $helpers)
    {
        $this->_helpers = $helpers;
    }

    /**
     * @return string
     */
    public function getScript() : string
    {
        return $this->_script;
    }


    /**
     * @param string $str
     * @param array|null $args
     * @return string
     */
    public function e(string $str = null, array $args = null) : string
    {
        return self::escape($str, $args);
    }

    /**
     * @todo furnish
     * @param string $str
     * @return string
     */
    public static function escape(string $str = null, array $args = null) : string
    {
        if(!$str) return '';

        if($args) {
            return htmlentities(sprintf($str, ...$args));
        }

        return htmlentities($str);
    }

    /**
     * @param mixed ...$names
     * @return null
     */
    public function helpers(...$names)
    {
        $helpers = $this->_helpers;
        if(count($names) == 0) {
            return $helpers;
        } else if(count($names) == 1) {
            return $helpers->get(current($names));
        } else {
            return $helpers->getMany($names);
        }
    }

    /**
     * @return array
     */
    public function toArray() : array
    {
        return $this->getArrayCopy();
    }

    /**
     * Delegates method calls to helpers
     *
     * @param $name
     * @param $arguments
     * @return mixed
     * @throws \Exception
     */
    public function __call($name, $arguments)
    {
        /** @var HelperRegistry $helpers */
        $helpers = $this->_helpers;
        return $helpers->__call($name, $arguments);
    }
}