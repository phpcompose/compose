<?php

namespace Compose\Container;

use Psr\Container\ContainerExceptionInterface;
use RuntimeException;
use Throwable;

class ContainerException extends RuntimeException implements ContainerExceptionInterface
{
    public static function fromResolution(string $target, Throwable $previous): self
    {
        $message = sprintf('Failed to resolve "%s": %s', $target, $previous->getMessage());
        return new self($message, 0, $previous);
    }

    public static function dueToInvalidDefinition(string $id, $definition): self
    {
        $type = is_object($definition) ? get_class($definition) : gettype($definition);
        return new self(sprintf('Service "%s" has invalid definition of type "%s".', $id, $type));
    }

    public static function fromParameter(string $target, string $parameter, ?Throwable $previous = null): self
    {
        $message = sprintf('Unable to resolve parameter "%s" while resolving "%s".', $parameter, $target);
        return new self($message, 0, $previous);
    }
}
