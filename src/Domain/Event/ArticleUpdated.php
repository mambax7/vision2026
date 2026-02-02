<?php

declare(strict_types=1);

namespace Vision2026\Domain\Event;

use Vision2026\Domain\ValueObject\ArticleId;
use Vision2026\Domain\ValueObject\ArticleTitle;

/**
 * Domain event: Article was updated.
 *
 * Fired when an article's content or title is changed.
 * Can be used to:
 * - Send notifications to subscribers
 * - Update search indexes
 * - Invalidate caches
 * - Track content changes for auditing
 */
final readonly class ArticleUpdated implements DomainEvent
{
    public function __construct(
        public ArticleId $articleId,
        public ArticleTitle $title,
        public \DateTimeImmutable $occurredAt,
    ) {}

    public function aggregateId(): string
    {
        return $this->articleId->toString();
    }

    public function eventName(): string
    {
        return 'article.updated';
    }

    public function occurredAt(): \DateTimeImmutable
    {
        return $this->occurredAt;
    }

    /**
     * Serialize event data for persistence.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'article_id' => $this->articleId->toString(),
            'title' => $this->title->value,
            'occurred_at' => $this->occurredAt->format(\DateTimeInterface::RFC3339),
        ];
    }
}
