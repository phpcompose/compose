<?php

namespace Compose\Template;

use Compose\Template\Helper\HelperRegistry;

class Template extends \ArrayObject
{
    public const string CONTENT = 'content';

    public ?string $layout = null;
    public ?string $title = null;

    public readonly HelperRegistry $helpers;

    private ?string $script;
    private array $sectionStack = [];
    private array $sections = [];
    /** Cache of public properties per class to avoid Reflection on hot paths */
    private static array $publicPropsCache = [];

    public function __construct(string $script, ?array $data = null)
    {
        parent::__construct($data ?? [], \ArrayObject::STD_PROP_LIST);
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

    public function e(?string $str = null, ?array $args = null): string
    {
        return self::escape($str, $args);
    }

    public static function escape(?string $str = null, ?array $args = null): string
    {
        if ($str === null) {
            return '';
        }

        if ($args) {
            try {
                $str = sprintf($str, ...$args);
            } catch (\ValueError $e) {
                // fall back to original string when placeholders mismatch
            }
        }

        return htmlspecialchars($str, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8', false);
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
        $this->sections[$name] = $value;
    }

    /**
     * Get or set a section.
     *
     * - As setter: $this->section('toolbar', $html) will set the section (allows null value)
     * - As getter: $this->section('toolbar') will return the section value or null if not present
     *
     * When setting, a dev-mode notice is emitted if a controller-provided value exists with the same name.
     * Returns $this when used as setter to allow chaining in templates.
     *
     * @param string $name
     * @param mixed|null $value
     * @return mixed|null|$this
     */
    public function section(string $name, mixed $value = null)
    {
        if (func_num_args() > 1) {
            // setter: store section value (no dev-mode warnings)
            $this->sections[$name] = $value;
            return $this;
        }

        // getter: section-only (no fallback to array-backed data)
        return array_key_exists($name, $this->sections) ? $this->sections[$name] : null;
    }

    public function get(string $name, mixed $default = null): mixed
    {
        if (array_key_exists($name, $this->sections)) {
            return $this->sections[$name];
        }

        // check declared public properties (cache class vars for speed)
        $class = static::class;
        if (!isset(self::$publicPropsCache[$class])) {
            self::$publicPropsCache[$class] = array_keys(get_class_vars($class));
        }
        if (in_array($name, self::$publicPropsCache[$class], true)) {
            return $this->$name;
        }

        // finally check array-backed view data without copying the whole array
        if ($this->offsetExists($name)) {
            return $this->offsetGet($name);
        }

        return $default;
    }

    public function has(string $name): bool
    {
        if (array_key_exists($name, $this->sections)) {
            return true;
        }

        $class = static::class;
        if (!isset(self::$publicPropsCache[$class])) {
            self::$publicPropsCache[$class] = array_keys(get_class_vars($class));
        }
        if (in_array($name, self::$publicPropsCache[$class], true)) {
            return true;
        }

        return $this->offsetExists($name);
    }

    public function content(): string
    {
        return (string) $this->get(self::CONTENT);
    }

    public function __call($name, $arguments)
    {
        return $this->helpers->__call($name, $arguments);
    }
}
