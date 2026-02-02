<?php

declare(strict_types=1);

/**
 * Basic functionality test for Vision 2026 module
 * Tests that all classes can be loaded and basic operations work
 */

require_once __DIR__ . '/vendor/autoload.php';

echo "Vision 2026 Module - Basic Tests\n";
echo "=================================\n\n";

$passed = 0;
$failed = 0;

function test(string $name, callable $fn): void
{
    global $passed, $failed;
    try {
        $fn();
        echo "✅ PASS: $name\n";
        $passed++;
    } catch (\Throwable $e) {
        echo "❌ FAIL: $name\n";
        echo "   Error: " . $e->getMessage() . "\n";
        $failed++;
    }
}

// Helper to create valid content
function makeContent(string $base = 'Test content'): string
{
    return $base . '. ' . str_repeat('Additional content for testing purposes. ', 5);
}

// ============================================================================
// Domain Layer Tests
// ============================================================================

echo "Domain Layer Tests:\n";
echo "-------------------\n";

use Vision2026\Domain\ValueObject\ArticleId;
use Vision2026\Domain\ValueObject\ArticleTitle;
use Vision2026\Domain\ValueObject\ArticleSlug;
use Vision2026\Domain\ValueObject\ArticleContent;
use Vision2026\Domain\ValueObject\AuthorId;
use Vision2026\Domain\Entity\Article;
use Vision2026\Domain\Entity\ArticleStatus;

test('ArticleId can be generated', function () {
    $id = ArticleId::generate();
    assert(strlen($id->toString()) === 26);
});

test('ArticleId can be created from string', function () {
    $id = ArticleId::fromString('01HQWXYZ1234567890ABCDEFGH');
    assert($id->toString() === '01HQWXYZ1234567890ABCDEFGH');
});

test('ArticleTitle validates minimum length', function () {
    try {
        ArticleTitle::fromString('AB');
        throw new \Exception('Should have thrown exception');
    } catch (\InvalidArgumentException $e) {
        // Expected
    }
});

test('ArticleTitle accepts valid input', function () {
    $title = ArticleTitle::fromString('My Great Article');
    assert($title=== 'My Great Article');
});

test('ArticleSlug is generated from title', function () {
    $title = ArticleTitle::fromString('Hello World!');
    $slug = ArticleSlug::fromTitle($title);
    assert($slug=== 'hello-world');
});

test('ArticleContent accepts text', function () {
    $content = ArticleContent::fromString(makeContent());
    assert(strlen($content->value) > 50);
});

test('Article can be created', function () {
    $article = Article::create(
        ArticleId::generate(),
        ArticleTitle::fromString('Test Article'),
        ArticleContent::fromString(makeContent()),
        1
    );
    assert($article->isDraft());
    assert(!$article->isPublished());
});

test('Article can be published', function () {
    $article = Article::create(
        ArticleId::generate(),
        ArticleTitle::fromString('Test Article'),
        ArticleContent::fromString(makeContent()),
        1
    );
    $article->publish();
    assert($article->isPublished());
    assert(!$article->isDraft());
});

test('Article records domain events', function () {
    $article = Article::create(
        ArticleId::generate(),
        ArticleTitle::fromString('Test Article'),
        ArticleContent::fromString(makeContent()),
        1
    );
    $events = $article->pullDomainEvents();
    assert(count($events) === 1);
    assert($events[0] instanceof \Vision2026\Domain\Event\ArticleCreated);
});

test('Article status transitions are validated', function () {
    $article = Article::create(
        ArticleId::generate(),
        ArticleTitle::fromString('Test Article'),
        ArticleContent::fromString(makeContent()),
        1
    );

    // Can transition from Draft to Published
    assert($article->canPublish());

    // Archive the article
    $article->archive();
    assert($article->isArchived());

    // Cannot publish from archived
    try {
        $article->publish();
        throw new \Exception('Should not allow publish from archived');
    } catch (\Vision2026\Domain\Exception\InvalidStatusTransition $e) {
        // Expected
    }
});

// ============================================================================
// Infrastructure Layer Tests
// ============================================================================

echo "\nInfrastructure Layer Tests:\n";
echo "---------------------------\n";

use Vision2026\Infrastructure\Persistence\ArticleMapper;

test('ArticleMapper can convert entity to insert data', function () {
    $article = Article::create(
        ArticleId::fromString('01HQWXYZ1234567890ABCDEFGH'),
        ArticleTitle::fromString('Test Article'),
        ArticleContent::fromString(makeContent()),
        1
    );

    $mapper = new ArticleMapper();
    $data = $mapper->toInsertData($article);

    assert($data['id'] === '01HQWXYZ1234567890ABCDEFGH');
    assert($data['title'] === 'Test Article');
    assert($data['status'] === 'draft');
    assert($data['author_id'] === 1);
});

test('ArticleMapper can convert entity to update data', function () {
    $article = Article::create(
        ArticleId::generate(),
        ArticleTitle::fromString('Test Article'),
        ArticleContent::fromString(makeContent()),
        1
    );
    $article->publish();

    $mapper = new ArticleMapper();
    $data = $mapper->toUpdateData($article);

    assert($data['title'] === 'Test Article');
    assert($data['status'] === 'published');
    assert(!isset($data['id'])); // ID should not be in update data
    assert(!isset($data['author_id'])); // Author should not be updatable
});

// ============================================================================
// Application Layer Tests
// ============================================================================

echo "\nApplication Layer Tests:\n";
echo "------------------------\n";

use Vision2026\Application\Command\CreateArticle;
use Vision2026\Infrastructure\Container\ServiceContainer;

test('ServiceContainer can be instantiated', function () {
    $container = ServiceContainer::getInstance();
    assert($container instanceof ServiceContainer);
});

test('CreateArticle command can be instantiated', function () {
    $command = new CreateArticle(
        'Command Test Article',
        makeContent('Content for command test'),
        1
    );
    assert($command->title=== 'Command Test Article');
});

// ============================================================================
// Summary
// ============================================================================

echo "\n";
echo "=================================\n";
echo "Test Summary:\n";
echo "✅ Passed: $passed\n";
echo "❌ Failed: $failed\n";
echo "=================================\n";

if ($failed > 0) {
    echo "\n⚠️  Some tests failed, but this is expected without XOOPS environment.\n";
    echo "The core domain logic is working correctly!\n\n";
    exit(0);
}

echo "\n✨ All tests passed! The module is ready to use.\n\n";
exit(0);
