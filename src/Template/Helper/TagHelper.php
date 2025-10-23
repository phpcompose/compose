<?php

namespace Compose\Template\Helper;

use Compose\Template\Template;

class TagHelper
{
    /**
     * HTML void elements per the HTML spec (lower-case).
     * These elements must not have end tags and cannot have child nodes.
     */
    protected static array $voidTags = [
        'area','base','br','col','embed','hr','img','input','link','meta','param','source','track','wbr'
    ];

    /**
     * Check whether a tag name is a void element.
     */
    public static function isVoidTag(string $name): bool
    {
        return in_array(strtolower($name), self::$voidTags, true);
    }

    public function __invoke(...$args)
    {
        return $this;
    }

    public function open(string $tagName, array $attributes = [], bool $void = false): string
    {
        if (!$tagName) {
            return '';
        }
        $isVoid = $void || self::isVoidTag($tagName);
        $attr = $this->attributeString($attributes);
        // HTML5: void elements are rendered without a trailing slash: <img src="...">
        return '<' . $tagName . $attr . ($isVoid ? '>' : '>');
    }

    public function close(string $tagName, bool $void = false): string
    {
        $isVoid = $void || self::isVoidTag($tagName);
        return ($tagName && !$isVoid) ? '</' . $tagName . '>' : '';
    }

    public function attributeString(array $attributes = []): string
    {
        if (!$attributes) {
            return '';
        }

        $str = '';
        foreach ($attributes as $key => $value) {
            // Validate attribute name
            if (!is_string($key) || !preg_match('/^[a-zA-Z_:][-a-zA-Z0-9_:.]*$/', $key)) {
                trigger_error('Invalid attribute name: ' . (string)$key, E_USER_WARNING);
                continue;
            }

            // Skip null or false
            if ($value === null || $value === false) {
                continue;
            }

            // Boolean attribute: render attribute name only
            if ($value === true) {
                $str .= $key . ' ';
                continue;
            }

            // Allow class as array -> join with spaces
            if ($key === 'class' && is_array($value)) {
                $value = implode(' ', array_filter($value, function($v) { return $v !== null && $v !== false && $v !== ''; }));
            }

            // Special-case: data-* attributes may accept arrays/objects -> json encode
            if (strpos($key, 'data-') === 0 && (is_array($value) || is_object($value))) {
                $encoded = json_encode($value, JSON_UNESCAPED_UNICODE);
                if ($encoded === false) {
                    trigger_error('Failed to json_encode value for ' . $key, E_USER_WARNING);
                    continue;
                }
                $value = $encoded;
            }

            // Scalars only from here
            if (!is_scalar($value)) {
                trigger_error('Value for attribute key {' . $key . '} must be scalar data type', E_USER_WARNING);
                continue;
            }

            // Use project's escape helper to ensure HTML safety. Template::escape should use ENT_QUOTES/UTF-8.
            $escaped = Template::escape((string)$value);
            $str .= "{$key}=\"{$escaped}\" ";
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

    public function tag(string $name, $inner = null, array $attributes = [], bool $void = false): string
    {
        // Always delegate to open/inner/close. close() will suppress closing tag for voids.
        return $this->open($name, $attributes, $void) .
            $this->toString($inner) .
            $this->close($name, $void);
    }
}
