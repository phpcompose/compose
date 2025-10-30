<?php

namespace Compose\Template;

use Compose\Template\Helper\HelperRegistry;

#[\AllowDynamicProperties]
class Template extends \ArrayObject
{
    public const string CONTENT = 'content';

    public ?string $layout = null;
    public ?string $title = null;
    public readonly HelperRegistry $helpers;

    private ?string $script;
    private array $blockStack = [];
    private array $blocks = [];

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
        $this->blockStack[] = $name;
        ob_start();
    }

    public function end(): void
    {
        if (empty($this->blockStack)) {
            throw new \LogicException('Cannot end a block without starting one.');
        }

        $name = array_pop($this->blockStack);
        $this->blocks[$name] = ob_get_clean();
    }

    public function block(string $name, mixed $value = null)
    {
        if (func_num_args() > 1) {
            // setter: store block value
            $this->blocks[$name] = $value;
            return $this;
        }

        // getter: block-only (no fallback to array-backed data)
        return array_key_exists($name, $this->blocks) ? $this->blocks[$name] : null;
    }

    public function content(): string
    {
        return (string) ($this->blocks[self::CONTENT] ?? '');
    }
}
