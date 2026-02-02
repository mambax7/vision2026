<?php

declare(strict_types=1);

namespace Vision2026\Tests\Unit\Domain\Entity;

use PHPUnit\Framework\TestCase;
use Vision2026\Domain\Entity\ArticleStatus;

/**
 * Unit tests for the ArticleStatus enum.
 *
 * Tests the state machine logic and valid transitions
 * between article statuses.
 */
final class ArticleStatusTest extends TestCase
{
    // =========================================================================
    // State Transition Tests
    // =========================================================================

    public function testDraftCanTransitionToPublished(): void
    {
        $this->assertTrue(
            ArticleStatus::Draft->canTransitionTo(ArticleStatus::Published)
        );
    }

    public function testDraftCanTransitionToArchived(): void
    {
        $this->assertTrue(
            ArticleStatus::Draft->canTransitionTo(ArticleStatus::Archived)
        );
    }

    public function testDraftCannotTransitionToDraft(): void
    {
        $this->assertFalse(
            ArticleStatus::Draft->canTransitionTo(ArticleStatus::Draft)
        );
    }

    public function testPublishedCanTransitionToDraft(): void
    {
        $this->assertTrue(
            ArticleStatus::Published->canTransitionTo(ArticleStatus::Draft)
        );
    }

    public function testPublishedCanTransitionToArchived(): void
    {
        $this->assertTrue(
            ArticleStatus::Published->canTransitionTo(ArticleStatus::Archived)
        );
    }

    public function testPublishedCannotTransitionToPublished(): void
    {
        $this->assertFalse(
            ArticleStatus::Published->canTransitionTo(ArticleStatus::Published)
        );
    }

    public function testArchivedCannotTransitionToAnyStatus(): void
    {
        $this->assertFalse(
            ArticleStatus::Archived->canTransitionTo(ArticleStatus::Draft)
        );
        $this->assertFalse(
            ArticleStatus::Archived->canTransitionTo(ArticleStatus::Published)
        );
        $this->assertFalse(
            ArticleStatus::Archived->canTransitionTo(ArticleStatus::Archived)
        );
    }

    // =========================================================================
    // Editability Tests
    // =========================================================================

    public function testDraftIsEditable(): void
    {
        $this->assertTrue(ArticleStatus::Draft->isEditable());
    }

    public function testPublishedIsEditable(): void
    {
        $this->assertTrue(ArticleStatus::Published->isEditable());
    }

    public function testArchivedIsNotEditable(): void
    {
        $this->assertFalse(ArticleStatus::Archived->isEditable());
    }

    // =========================================================================
    // String Conversion Tests
    // =========================================================================

    public function testDraftToString(): void
    {
        $this->assertSame('draft', ArticleStatus::Draft->toString());
    }

    public function testPublishedToString(): void
    {
        $this->assertSame('published', ArticleStatus::Published->toString());
    }

    public function testArchivedToString(): void
    {
        $this->assertSame('archived', ArticleStatus::Archived->toString());
    }

    public function testFromStringDraft(): void
    {
        $status = ArticleStatus::fromString('draft');
        $this->assertSame(ArticleStatus::Draft, $status);
    }

    public function testFromStringPublished(): void
    {
        $status = ArticleStatus::fromString('published');
        $this->assertSame(ArticleStatus::Published, $status);
    }

    public function testFromStringArchived(): void
    {
        $status = ArticleStatus::fromString('archived');
        $this->assertSame(ArticleStatus::Archived, $status);
    }

    public function testFromStringWithInvalidStatusThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid status: invalid');

        ArticleStatus::fromString('invalid');
    }

    // =========================================================================
    // Enum Cases Tests
    // =========================================================================

    public function testAllCasesArePresent(): void
    {
        $cases = ArticleStatus::cases();

        $this->assertCount(3, $cases);
        $this->assertContains(ArticleStatus::Draft, $cases);
        $this->assertContains(ArticleStatus::Published, $cases);
        $this->assertContains(ArticleStatus::Archived, $cases);
    }

    public function testEnumEquality(): void
    {
        $draft1 = ArticleStatus::Draft;
        $draft2 = ArticleStatus::Draft;
        $published = ArticleStatus::Published;

        $this->assertSame($draft1, $draft2);
        $this->assertNotSame($draft1, $published);
    }
}
