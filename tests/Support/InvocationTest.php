<?php

declare(strict_types=1);

namespace Tests\Support;

use Compose\Support\Invocation;
use DateTimeInterface;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class InvocationTest extends TestCase
{
    public function testFromCallableReturnsInvocation(): void
    {
        $invocation = Invocation::fromCallable(fn() => 'ok');

        $this->assertInstanceOf(Invocation::class, $invocation);
    }

    public function testFromCallableReturnsNullForNonCallable(): void
    {
        $this->assertNull(Invocation::fromCallable('not_callable'));
    }

    public function testInvokeUsesStoredParameters(): void
    {
        $invocation = new Invocation(fn(int $a, int $b) => $a + $b, [2, 3]);

        $this->assertSame(5, $invocation());
    }

    public function testInvokeThrowsWhenTooFewArguments(): void
    {
        $invocation = new Invocation(fn(int $a, int $b) => $a + $b);

        $this->expectException(InvalidArgumentException::class);
        $invocation(1);
    }

    public function testInvokeThrowsWhenTooManyArguments(): void
    {
        $invocation = new Invocation(fn(int $a) => $a * 2);

        $this->expectException(InvalidArgumentException::class);
        $invocation(1, 2);
    }

    public function testInvokeSupportsVariadicArguments(): void
    {
        $invocation = new Invocation(fn(string ...$messages) => implode(', ', $messages));

        $this->assertSame('a, b, c', $invocation('a', 'b', 'c'));
    }

    public function testGetArgumentTypeAtIndexReturnsTypeName(): void
    {
        $invocation = new Invocation(function (DateTimeInterface $date): void {
        });

        $this->assertSame(DateTimeInterface::class, $invocation->getArgumentTypeAtIndex(0));
    }

    public function testGetArgumentTypeAtIndexReturnsNullWhenTypeMissing(): void
    {
        $invocation = new Invocation(function ($value): void {
        });

        $this->assertNull($invocation->getArgumentTypeAtIndex(0));
    }

    public function testGetArgumentTypeAtIndexSupportsUnionTypes(): void
    {
        $invocation = new Invocation(function (int|float $number): void {
        });

        $this->assertSame('int|float', $invocation->getArgumentTypeAtIndex(0));
    }
}
