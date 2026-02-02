<?php

declare(strict_types=1);

namespace Vision2026\Domain\ValueObject;

use InvalidArgumentException;

/**
 * URL-safe article slug value object.
 *
 * A slug is the URL-friendly version of the title:
 * - "Hello World!" becomes "hello-world"
 * - "PHP 8.2: What's New?" becomes "php-82-whats-new"
 *
 * Slugs are used for SEO-friendly URLs like:
 * /articles/php-82-whats-new
 */
final readonly class ArticleSlug
{
    private const MAX_LENGTH = 220;
    private const PATTERN = '/^[a-z0-9]+(?:-[a-z0-9]+)*$/';

    private function __construct(
        public string $value
    ) {}

    /**
     * Generate slug from article title.
     */
    public static function fromTitle(ArticleTitle $title): self
    {
        $slug = self::slugify($title->value);

        return new self($slug);
    }

    /**
     * Create from existing slug string.
     *
     * @throws InvalidArgumentException if format is invalid
     */
    public static function fromString(string $slug): self
    {
        $slug = strtolower(trim($slug));

        if (!self::isValid($slug)) {
            throw new InvalidArgumentException(
                sprintf('Invalid slug format: "%s"', $slug)
            );
        }

        return new self($slug);
    }

    /**
     * Check equality with another slug.
     */
    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }

    /**
     * Create a unique variant by appending a suffix.
     */
    public function makeUnique(int $suffix): self
    {
        $newValue = $this->value . '-' . $suffix;

        if (strlen($newValue) > self::MAX_LENGTH) {
            // Truncate original to make room for suffix
            $maxOriginal = self::MAX_LENGTH - strlen('-' . $suffix);
            $newValue = substr($this->value, 0, $maxOriginal) . '-' . $suffix;
        }

        return new self($newValue);
    }

    /**
     * Convert a string to a URL-safe slug.
     */
    private static function slugify(string $text): string
    {
        // Convert to lowercase
        $slug = mb_strtolower($text, 'UTF-8');

        // Transliterate accented characters
        $slug = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $slug) ?: $slug;

        // Replace non-alphanumeric characters with hyphens
        $slug = preg_replace('/[^a-z0-9]+/', '-', $slug);

        // Remove leading/trailing hyphens
        $slug = trim($slug, '-');

        // Collapse multiple hyphens
        $slug = preg_replace('/-+/', '-', $slug);

        // Truncate to max length
        if (strlen($slug) > self::MAX_LENGTH) {
            $slug = substr($slug, 0, self::MAX_LENGTH);
            // Don't end with a hyphen
            $slug = rtrim($slug, '-');
        }

        return $slug;
    }

    /**
     * Validate slug format.
     */
    private static function isValid(string $slug): bool
    {
        if ($slug === '') {
            return false;
        }

        if (strlen($slug) > self::MAX_LENGTH) {
            return false;
        }

        return preg_match(self::PATTERN, $slug) === 1;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
