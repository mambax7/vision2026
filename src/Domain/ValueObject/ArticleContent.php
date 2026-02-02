<?php

declare(strict_types=1);

namespace Vision2026\Domain\ValueObject;

use InvalidArgumentException;

/**
 * Article content value object.
 *
 * Encapsulates the article body with its format (HTML, Markdown, etc.).
 * Provides methods for content manipulation and validation.
 */
final readonly class ArticleContent
{
    public const MIN_LENGTH = 50;
    public const MAX_LENGTH = 1_000_000; // ~1MB

    public const FORMAT_HTML = 'html';
    public const FORMAT_MARKDOWN = 'markdown';
    public const FORMAT_TEXT = 'text';

    private const VALID_FORMATS = [
        self::FORMAT_HTML,
        self::FORMAT_MARKDOWN,
        self::FORMAT_TEXT,
    ];

    private function __construct(
        public string $value,
        public string $format,
    ) {}

    /**
     * Create content from string with format detection.
     *
     * @throws InvalidArgumentException if validation fails
     */
    public static function fromString(
        string $content,
        string $format = self::FORMAT_HTML
    ): self {
        self::validate($content, $format);

        return new self(
            value: $content,
            format: $format,
        );
    }

    /**
     * Validate content constraints.
     *
     * @throws InvalidArgumentException
     */
    private static function validate(string $content, string $format): void
    {
        $length = mb_strlen($content, 'UTF-8');

        if ($length < self::MIN_LENGTH) {
            throw new InvalidArgumentException(
                sprintf(
                    'Content must be at least %d characters, got %d',
                    self::MIN_LENGTH,
                    $length
                )
            );
        }

        if ($length > self::MAX_LENGTH) {
            throw new InvalidArgumentException(
                sprintf(
                    'Content must not exceed %d characters, got %d',
                    self::MAX_LENGTH,
                    $length
                )
            );
        }

        if (!in_array($format, self::VALID_FORMATS, true)) {
            throw new InvalidArgumentException(
                sprintf(
                    'Invalid format "%s", must be one of: %s',
                    $format,
                    implode(', ', self::VALID_FORMATS)
                )
            );
        }
    }

    /**
     * Check equality with another content.
     */
    public function equals(self $other): bool
    {
        return $this->value === $other->value
            && $this->format === $other->format;
    }

    /**
     * Get character count.
     */
    public function length(): int
    {
        return mb_strlen($this->value, 'UTF-8');
    }

    /**
     * Get word count.
     */
    public function wordCount(): int
    {
        $text = strip_tags($this->value);
        $text = preg_replace('/\s+/', ' ', $text);
        return str_word_count($text);
    }

    /**
     * Get estimated reading time in minutes.
     */
    public function readingTime(int $wordsPerMinute = 200): int
    {
        return max(1, (int) ceil($this->wordCount() / $wordsPerMinute));
    }

    /**
     * Generate excerpt/summary.
     */
    public function excerpt(int $maxLength = 160, string $suffix = '...'): string
    {
        $text = strip_tags($this->value);
        $text = preg_replace('/\s+/', ' ', $text);
        $text = trim($text);

        if (mb_strlen($text, 'UTF-8') <= $maxLength) {
            return $text;
        }

        $excerpt = mb_substr($text, 0, $maxLength - mb_strlen($suffix), 'UTF-8');

        // Break at word boundary
        $lastSpace = mb_strrpos($excerpt, ' ', 0, 'UTF-8');
        if ($lastSpace !== false && $lastSpace > $maxLength * 0.7) {
            $excerpt = mb_substr($excerpt, 0, $lastSpace, 'UTF-8');
        }

        return $excerpt . $suffix;
    }

    /**
     * Convert to plain text (strip HTML).
     */
    public function toPlainText(): string
    {
        $text = strip_tags($this->value);
        $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = preg_replace('/\s+/', ' ', $text);
        return trim($text);
    }

    /**
     * Check if content is in HTML format.
     */
    public function isHtml(): bool
    {
        return $this->format === self::FORMAT_HTML;
    }

    /**
     * Check if content is in Markdown format.
     */
    public function isMarkdown(): bool
    {
        return $this->format === self::FORMAT_MARKDOWN;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
