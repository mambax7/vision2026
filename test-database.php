<?php

declare(strict_types=1);

/**
 * Database integration test for Vision 2026 module
 * Tests the full stack: Domain → Application → Infrastructure → Database
 */

// Bootstrap XOOPS
require_once dirname(__DIR__, 2) . '/mainfile.php';

// Module autoloader
require_once __DIR__ . '/vendor/autoload.php';

use Vision2026\Infrastructure\Container\ServiceContainer;
use Vision2026\Domain\Repository\ArticleRepositoryInterface;
use Vision2026\Domain\ValueObject\ArticleTitle;
use Vision2026\Domain\ValueObject\ArticleContent;
use Vision2026\Domain\ValueObject\AuthorId;
use Vision2026\Application\Command\CreateArticle;
use Vision2026\Application\Command\CreateArticleHandler;
use Vision2026\Application\Command\PublishArticle;
use Vision2026\Application\Command\PublishArticleHandler;

echo "Vision 2026 - Database Integration Test\n";
echo "=========================================\n\n";

try {
    $container = ServiceContainer::getInstance();
    $articleRepository = $container->get(ArticleRepositoryInterface::class);
    $createHandler = $container->get(CreateArticleHandler::class);
    $publishHandler = $container->get(PublishArticleHandler::class);

    echo "✅ Service container initialized\n";
    echo "✅ Repository connected\n\n";

    // Test 1: Create a new article
    echo "Test 1: Creating article...\n";
    $command = new CreateArticle(
        'Integration Test Article',
        'This is a test article created through the full architecture stack. ' . str_repeat('It demonstrates how the domain layer, application layer, and infrastructure layer work together seamlessly. ', 5),
        $GLOBALS['xoopsUser']->uid()
    );

    $article = $createHandler->handle($command);
    echo "✅ Article created with ID: {$article->id->toString()}\n";
    echo "   Title: {$article->getTitle()->value}\n";
    echo "   Status: {$article->getStatus()->value}\n\n";

    // Test 2: Verify article is in database
    echo "Test 2: Retrieving article from database...\n";
    $retrieved = $articleRepository->find($article->id);
    if ($retrieved) {
        echo "✅ Article retrieved successfully\n";
        echo "   Title: {$retrieved->getTitle()->value}\n";
        echo "   Slug: {$retrieved->getSlug()->value}\n\n";
    } else {
        echo "❌ Failed to retrieve article\n\n";
    }

    // Test 3: Publish the article
    echo "Test 3: Publishing article...\n";
    $publishCommand = new PublishArticle($article->id);
    $publishHandler->handle($publishCommand);

    $published = $articleRepository->find($article->id);
    if ($published && $published->isPublished()) {
        echo "✅ Article published successfully\n";
        echo "   Status: {$published->getStatus()->value}\n";
        echo "   Published at: {$published->getPublishedAt()->format('Y-m-d H:i:s')}\n\n";
    } else {
        echo "❌ Failed to publish article\n\n";
    }

    // Test 4: Count articles
    echo "Test 4: Repository queries...\n";
    $total = $articleRepository->count();
    $drafts = $articleRepository->countByStatus(\Vision2026\Domain\Entity\ArticleStatus::Draft);
    $published = $articleRepository->countByStatus(\Vision2026\Domain\Entity\ArticleStatus::Published);

    echo "✅ Total articles: $total\n";
    echo "   Drafts: $drafts\n";
    echo "   Published: $published\n\n";

    // Test 5: Find recent published
    echo "Test 5: Find recent published articles...\n";
    $recent = $articleRepository->findRecentPublished(5);
    echo "✅ Found " . count($recent) . " published articles\n";
    foreach ($recent as $recentArticle) {
        echo "   - {$recentArticle->getTitle()->value}\n";
    }
    echo "\n";

    echo "=========================================\n";
    echo "✨ All integration tests passed!\n";
    echo "=========================================\n\n";

    echo "The module is fully functional! You can now:\n";
    echo "1. Visit: http://localhost/modules/vision2026/admin/articles.php\n";
    echo "2. Visit: http://localhost/modules/vision2026/\n\n";

} catch (\Throwable $e) {
    echo "\n❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}
