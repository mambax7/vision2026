<?php

declare(strict_types=1);

namespace Vision2026\Domain\ValueObject;

use InvalidArgumentException;

/**
 * Article title value object.
 *
 * Demonstrates value object pattern:
 * - Immutable (readonly class)
 * - Self-validating (factory method)
 * - Behavior-rich (provides methods, not just data)
 *
 * Once created, an ArticleTitle is guaranteed to be valid.
 * This eliminates validation scattered throughout the codebase.
 */
final readonly class ArticleTitle
{
    public const MIN_LENGTH = 3;
    public const MAX_LENGTH = 200;

    private function __construct(
        public string $value
    ) {}

    /**
     * Create a new ArticleTitle from string.
     *
     * @throws InvalidArgumentException if validation fails
     */
    public static function fromString(string $title): self
    {
        $title = self::normalize($title);

        self::validate($title);

        return new self($title);
    }

    /**
     * Normalize the title string.
     */
    private static function normalize(string $title): string
    {
        // Trim whitespace
        $title = trim($title);

        // Normalize multiple spaces to single space
        $title = preg_replace('/\s+/', ' ', $title);

        return $title;
    }

    /**
     * Validate title constraints.
     *
     * @throws InvalidArgumentException
     */
    private static function validate(string $title): void
    {
        $length = mb_strlen($title, 'UTF-8');

        if ($length < self::MIN_LENGTH) {
            throw new InvalidArgumentException(
                sprintf(
                    'Article title must be at least %d characters, got %d',
                    self::MIN_LENGTH,
                    $length
                )
            );
        }

        if ($length > self::MAX_LENGTH) {
            throw new InvalidArgumentException(
                sprintf(
                    'Article title must not exceed %d characters, got %d',
                    self::MAX_LENGTH,
                    $length
                )
            );
        }

        // Additional validation could go here:
        // - No HTML tags
        // - No control characters
        // - etc.
    }

    /**
     * Check equality with another title.
     */
    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }

    /**
     * Get character count.
     */
    public function length(): int
    {
        return mb_strlen($this->value, 'UTF-8');
    }

    /**
     * Get truncated version for previews.
     */
    public function truncate(int $maxLength = 50, string $suffix = '...'): string
    {
        if ($this->length() <= $maxLength) {
            return $this->value;
        }

        $truncated = mb_substr($this->value, 0, $maxLength - mb_strlen($suffix), 'UTF-8');

        // Try to break at word boundary
        $lastSpace = mb_strrpos($truncated, ' ', 0, 'UTF-8');
        if ($lastSpace !== false && $lastSpace > $maxLength * 0.7) {
            $truncated = mb_substr($truncated, 0, $lastSpace, 'UTF-8');
        }

        return $truncated . $suffix;
    }

    /**
     * Generate URL-safe slug from title.
     */
    public function toSlug(): string
    {
        $slug = mb_strtolower($this->value, 'UTF-8');

        // Replace accented characters
        $slug = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $slug);

        // Replace non-alphanumeric with hyphen
        $slug = preg_replace('/[^a-z0-9]+/', '-', $slug);

        // Remove leading/trailing hyphens
        $slug = trim($slug, '-');

        return $slug;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
