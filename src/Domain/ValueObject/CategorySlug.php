<?php

declare(strict_types=1);

namespace Vision2026\Domain\ValueObject;

use InvalidArgumentException;

/**
 * Category slug value object.
 */
final readonly class CategorySlug
{
    private function __construct(
        public string $value
    ) {}

    public static function fromString(string $slug): self
    {
        $slug = trim(strtolower($slug));

        if (empty($slug)) {
            throw new InvalidArgumentException('Category slug cannot be empty');
        }

        // Basic slug validation
        if (!preg_match('/^[a-z0-9-]+$/', $slug)) {
            throw new InvalidArgumentException('Category slug must contain only lowercase letters, numbers, and hyphens');
        }

        return new self($slug);
    }

    public static function fromName(CategoryName $name): self
    {
        $slug = strtolower($name->value);
        $slug = preg_replace('/[^a-z0-9]+/', '-', $slug);
        $slug = trim($slug, '-');

        return new self($slug);
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
