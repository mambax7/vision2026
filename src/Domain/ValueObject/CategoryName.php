<?php

declare(strict_types=1);

namespace Vision2026\Domain\ValueObject;

use InvalidArgumentException;

/**
 * Category name value object.
 */
final readonly class CategoryName
{
    public const MIN_LENGTH = 2;
    public const MAX_LENGTH = 100;

    private function __construct(
        public string $value
    ) {}

    public static function fromString(string $name): self
    {
        $name = trim($name);

        if (strlen($name) < self::MIN_LENGTH) {
            throw new InvalidArgumentException(
                sprintf('Category name must be at least %d characters', self::MIN_LENGTH)
            );
        }

        if (strlen($name) > self::MAX_LENGTH) {
            throw new InvalidArgumentException(
                sprintf('Category name must not exceed %d characters', self::MAX_LENGTH)
            );
        }

        return new self($name);
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
