<?php
declare(strict_types=1);

namespace Compose\Support;

use Laminas\Stdlib\ArrayUtils;
use RuntimeException;

/**
 * Class Configuration
 * @package Compose\Support
 */
class Configuration extends \ArrayObject
{
    /**
     * When true the configuration cannot be mutated.
     */
    protected bool $readOnly = false;
    /**
     * Configuration constructor.
     */
    public function __construct(?array $config = null, bool $readOnly = false)
    {
        parent::__construct($config ?? []);
        $this->readOnly = $readOnly;
    }


    /**
     * Merge configuration from a PHP file that returns an array.
     *
     * @param string $file
     * @throws RuntimeException when file missing or does not return an array
     */
    public function mergeFromFile(string $file): void
    {
        $this->ensureMutable();
        if (!is_file($file)) {
            throw new RuntimeException('File not found: ' . $file);
        }

        $config = include $file;
        if (!is_array($config)) {
            throw new RuntimeException('Config file does not return array.');
        }

        $this->merge($config);
    }

    /**
     * Retrieve a nested value from the configuration using a dot-separated path.
     *
     * @param string $keyPath
     * @param mixed $default
     * @param string $keyPathSeparator
     * @return mixed
     */
    public function getNestedValue(string $keyPath, mixed $default = null, string $keyPathSeparator = '.'): mixed
    {
        if ($keyPath === '') {
            return $default;
        }

        $keys = explode($keyPathSeparator, $keyPath);

        $var = $this;
        foreach ($keys as $key) {
            // support arrays and ArrayAccess (ArrayObject)
            if (is_array($var) || $var instanceof \ArrayAccess) {
                if (!isset($var[$key])) {
                    return $default;
                }
                $var = $var[$key];
                continue;
            }

            return $default;
        }

        return $var;
    }

    /**
     * @param array $array
     */
    public function merge(array $array): void
    {
        $this->ensureMutable();
        $merged = ArrayUtils::merge($this->getArrayCopy(), $array);
        // replace internal array with the merged result
        $this->exchangeArray($merged);
    }

    /**
     * Return whether the configuration is read-only.
     */
    public function isReadOnly(): bool
    {
        return $this->readOnly;
    }

    /**
     * Ensure the configuration can be modified.
     *
     * @throws RuntimeException
     */
    protected function ensureMutable(): void
    {
        if ($this->readOnly) {
            throw new RuntimeException('Configuration is read-only');
        }
    }

    // Override mutating methods to enforce read-only
    public function offsetSet(mixed $index, mixed $newval): void
    {
        $this->ensureMutable();
        parent::offsetSet($index, $newval);
    }

    public function offsetUnset(mixed $index): void
    {
        $this->ensureMutable();
        parent::offsetUnset($index);
    }

    public function append(mixed $value): void
    {
        $this->ensureMutable();
        parent::append($value);
    }

    public function exchangeArray(array|object $array): array
    {
        $this->ensureMutable();
        return parent::exchangeArray($array);
    }
}