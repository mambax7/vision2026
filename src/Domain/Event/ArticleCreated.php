<?php

declare(strict_types=1);

namespace Vision2026\Domain\Event;

use Vision2026\Domain\ValueObject\ArticleId;
use Vision2026\Domain\ValueObject\ArticleTitle;
use Vision2026\Domain\ValueObject\AuthorId;

/**
 * Event raised when a new article is created.
 *
 * Possible reactions:
 * - Log the creation
 * - Notify admin of new content
 * - Update article count statistics
 */
final readonly class ArticleCreated implements DomainEvent
{
    public function __construct(
        public ArticleId $articleId,
        public ArticleTitle $title,
        public AuthorId $authorId,
        public \DateTimeImmutable $occurredAt,
    ) {}

    public function occurredAt(): \DateTimeImmutable
    {
        return $this->occurredAt;
    }

    public function aggregateId(): string
    {
        return $this->articleId->toString();
    }

    public function eventName(): string
    {
        return 'article.created';
    }

    public function toArray(): array
    {
        return [
            'article_id' => $this->articleId->toString(),
            'title' => $this->title->value,
            'author_id' => $this->authorId->value,
            'occurred_at' => $this->occurredAt->format('c'),
        ];
    }
}
