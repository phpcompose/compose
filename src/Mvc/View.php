<?php

namespace Compose\Mvc;

use Compose\Mvc\Helper\HelperRegistry;

class View extends \ArrayObject
{
    public const CONTENT = 'content';

    public ?string $layout = null;
    public ?string $title = null;

    private HelperRegistry $helpers;
    private ?string $script;
    private array $sectionStack = [];
    private array $sections = [];

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

    public function start(string $name): void
    {
        $this->sectionStack[] = $name;
        ob_start();
    }

    public function end(): void
    {
        if (empty($this->sectionStack)) {
            throw new \LogicException('Cannot end a section without starting one.');
        }

        $name = array_pop($this->sectionStack);
        $this->sections[$name] = ob_get_clean();
    }

    public function set(string $name, mixed $value): void
    {
        if ($name === self::CONTENT) {
            $this->sections[self::CONTENT] = (string) $value;
            return;
        }

        $this->sections[$name] = $value;
    }

    public function get(string $name, mixed $default = null): mixed
    {
        if (array_key_exists($name, $this->sections)) {
            return $this->sections[$name];
        }

        $data = $this->getArrayCopy();
        return array_key_exists($name, $data) ? $data[$name] : $default;
    }

    public function has(string $name): bool
    {
        return array_key_exists($name, $this->sections) || array_key_exists($name, $this->getArrayCopy());
    }

    public function content(): ?string
    {
        $value = $this->get(self::CONTENT);
        return is_string($value) ? $value : null;
    }

    public function toArray(): array
    {
        $data = $this->getArrayCopy();
        $data['view'] = $this;

        return $data;
    }

    public function __call($name, $arguments)
    {
        return $this->helpers->__call($name, $arguments);
    }
}
