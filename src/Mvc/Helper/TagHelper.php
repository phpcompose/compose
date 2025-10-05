<?php

namespace Compose\Mvc\Helper;

use Compose\Mvc\View;

class TagHelper
{
    public function __invoke(...$args)
    {
        return $this;
    }

    public function open(string $tagName, array $attributes = [], bool $void = false): string
    {
        if (!$tagName) {
            return '';
        }
        $attr = $this->attributeString($attributes);
        return '<' . $tagName . $attr . ($void ? ' />' : '>');
    }

    public function close(string $tagName, bool $void = false): string
    {
        return ($tagName && !$void) ? '</' . $tagName . '>' : '';
    }

    public function attributeString(array $attributes = []): string
    {
        if (!$attributes) {
            return '';
        }
        $str = '';
        foreach ($attributes as $key => $value) {
            if ($value === null || $value === false) {
                // Omit attribute if value is null/false
                continue;
            }
            if ($value === true) {
                // Boolean attribute, e.g., disabled
                $str .= $key . ' ';
                continue;
            }
            if (!is_scalar($value)) {
                trigger_error('Value for attribute key {' . $key . '} must be scalar data type');
                continue;
            }
            $value = View::escape($value);
            $str .= "{$key}=\"{$value}\" ";
        }
        return $str ? ' ' . trim($str) : '';
    }

    public function toString($arg): string
    {
        if (is_scalar($arg)) {
            return (string) $arg;
        }
        if (is_array($arg) || $arg instanceof \Iterator) {
            $buf = '';
            foreach ($arg as $val) {
                $buf .= $this->toString($val);
            }
            return $buf;
        }
        if (is_object($arg) && method_exists($arg, '__toString')) {
            return (string) $arg;
        }
        return '';
    }

    public function tag(string $name, $inner = null, array $attributes = []): string
    {
        return $this->open($name, $attributes) .
            $this->toString($inner) .
            $this->close($name);
    }
}
