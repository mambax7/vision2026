<?php

declare(strict_types=1);

namespace Vision2026\Domain\Entity;

/**
 * Article lifecycle status as a PHP 8.1 enum.
 *
 * Encapsulates the valid states and allowed transitions.
 * This is a key example of using modern PHP features
 * to make invalid states impossible.
 */
enum ArticleStatus: string
{
    case Draft = 'draft';
    case Published = 'published';
    case Archived = 'archived';

    /**
     * Check if transition to target status is allowed.
     *
     * State machine rules:
     * - Draft → Published (publish)
     * - Draft → Archived (discard)
     * - Published → Draft (unpublish/retract)
     * - Published → Archived (archive)
     * - Archived → (terminal state)
     */
    public function canTransitionTo(self $target): bool
    {
        return match ($this) {
            self::Draft => in_array($target, [self::Published, self::Archived], true),
            self::Published => in_array($target, [self::Draft, self::Archived], true),
            self::Archived => false, // Terminal state
        };
    }

    /**
     * Get all allowed transitions from current state.
     *
     * @return list<self>
     */
    public function allowedTransitions(): array
    {
        return match ($this) {
            self::Draft => [self::Published, self::Archived],
            self::Published => [self::Draft, self::Archived],
            self::Archived => [],
        };
    }

    /**
     * Human-readable label for display.
     */
    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::Published => 'Published',
            self::Archived => 'Archived',
        };
    }

    /**
     * CSS class for status badge styling.
     */
    public function cssClass(): string
    {
        return match ($this) {
            self::Draft => 'status-draft',
            self::Published => 'status-published',
            self::Archived => 'status-archived',
        };
    }

    /**
     * Icon identifier for UI display.
     */
    public function icon(): string
    {
        return match ($this) {
            self::Draft => 'edit',
            self::Published => 'check-circle',
            self::Archived => 'archive',
        };
    }

    /**
     * Whether this status represents publicly visible content.
     */
    public function isPublic(): bool
    {
        return $this === self::Published;
    }

    /**
     * Whether this status allows editing.
     * 
     * For CMS usability, both Draft and Published articles can be edited.
     * Only Archived articles are read-only.
     */
    public function isEditable(): bool
    {
        return $this !== self::Archived;
    }
}
