<?php

namespace Compose\Mvc;

use Compose\Mvc\Helper\HelperRegistry;

class View extends \ArrayObject
{
    public $layout = null;
    public $title = null;

    protected HelperRegistry $helpers;
    protected ?string $script = null;

    public function __construct(string $script, array $data = null)
    {
        parent::__construct($data ?? []);
        $this->script = $script;
    }

    public function setHelperRegistry(HelperRegistry $helpers): void
    {
        $this->helpers = $helpers;
    }

    public function getScript(): string
    {
        return $this->script ?? '';
    }

    public function e(string $str = null, array $args = null): string
    {
        return self::escape($str, $args);
    }

    public static function escape(string $str = null, array $args = null): string
    {
        if (!$str) {
            return '';
        }

        if ($args) {
            return htmlentities(sprintf($str, ...$args));
        }

        return htmlentities($str);
    }

    public function helpers(...$names)
    {
        $helpers = $this->helpers;
        if (count($names) === 0) {
            return $helpers;
        }

        if (count($names) === 1) {
            return $helpers->get(current($names));
        }

        return $helpers->getMany($names);
    }

    public function toArray(): array
    {
        return $this->getArrayCopy();
    }

    public function __get($name)
    {
        return $this->_data[$name] ?? null;
    }

    public function __set($name, $value): void
    {
        $this->_data[$name] = $value;
    }

    public function __unset($name): void
    {
        if (isset($this->_data[$name])) {
            $this->_data[$name] = null;
            unset($this->_data[$name]);
        }
    }

    public function __call($name, $arguments)
    {
        return $this->helpers->__call($name, $arguments);
    }
}
