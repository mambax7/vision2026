<?php

declare(strict_types=1);

namespace Vision2026\Application\Command;

/**
 * Command to create a new article.
 *
 * Commands are simple DTOs (Data Transfer Objects) that carry
 * the intent and data needed to perform an action. They are:
 *
 * - Immutable (readonly)
 * - Named as imperative verbs (CreateArticle, PublishArticle)
 * - Contain only primitive data (no domain objects)
 *
 * The handler will validate and transform this data into domain objects.
 */
final readonly class CreateArticle
{
    /**
     * @param string      $title      Article title (3-200 chars)
     * @param string      $content    Article content (min 50 chars)
     * @param int         $authorId   XOOPS user ID of the author
     * @param int|null    $categoryId Optional category ID
     * @param string      $format     Content format: html, markdown, text
     */
    public function __construct(
        public string $title,
        public string $content,
        public int $authorId,
        public ?int $categoryId = null,
        public string $format = 'html',
    ) {}

    /**
     * Create from form/request data.
     *
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            title: (string) ($data['title'] ?? ''),
            content: (string) ($data['content'] ?? ''),
            authorId: (int) ($data['author_id'] ?? 0),
            categoryId: isset($data['category_id']) ? (int) $data['category_id'] : null,
            format: (string) ($data['format'] ?? 'html'),
        );
    }
}
