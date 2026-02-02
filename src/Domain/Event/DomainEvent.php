<?php

declare(strict_types=1);

namespace Vision2026\Domain\Event;

/**
 * Marker interface for domain events.
 *
 * Domain events represent something significant that happened in the domain.
 * They are:
 * - Immutable: once created, they never change
 * - Past tense: ArticlePublished, not PublishArticle
 * - Business-focused: named in domain language
 *
 * Events decouple the core domain from side effects like:
 * - Sending notifications
 * - Updating search indexes
 * - Logging activity
 * - Syncing with external systems
 */
interface DomainEvent
{
    /**
     * When the event occurred.
     */
    public function occurredAt(): \DateTimeImmutable;

    /**
     * Get the aggregate ID this event belongs to.
     */
    public function aggregateId(): string;

    /**
     * Get the event name for serialization/routing.
     */
    public function eventName(): string;

    /**
     * Serialize event data for storage/transmission.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array;
}
