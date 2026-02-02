<?php

declare(strict_types=1);

namespace Vision2026\Domain\ValueObject;

use InvalidArgumentException;

/**
 * Author ID value object.
 *
 * Wraps the XOOPS user ID to provide type safety
 * and domain-specific semantics.
 */
final readonly class AuthorId
{
    private function __construct(
        public int $value
    ) {}

    /**
     * Create from integer.
     *
     * @throws InvalidArgumentException if ID is invalid
     */
    public static function fromInt(int $id): self
    {
        if ($id <= 0) {
            throw new InvalidArgumentException(
                sprintf('Author ID must be positive, got: %d', $id)
            );
        }

        return new self($id);
    }

    /**
     * Check equality with another AuthorId.
     */
    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }

    public function __toString(): string
    {
        return (string) $this->value;
    }
}
