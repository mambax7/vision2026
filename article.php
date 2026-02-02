<?php

declare(strict_types=1);

/**
 * Vision 2026 Module - Single Article Display
 *
 * Displays a single article by slug or ID.
 *
 * URL patterns:
 *   /modules/vision2026/article.php?slug=my-article-title
 *   /modules/vision2026/article.php?id=01ARZ3NDEKTSV4RRFFQ69G5FAV
 */

// XOOPS header
require_once dirname(__DIR__, 2) . '/mainfile.php';
require_once XOOPS_ROOT_PATH . '/header.php';

// Module autoloader
$moduleDirName = basename(__DIR__);
require_once __DIR__ . '/vendor/autoload.php';

use Vision2026\Infrastructure\Container\ServiceContainer;
use Vision2026\Infrastructure\Demo\DemoDataProvider;
use Vision2026\Domain\Repository\ArticleRepositoryInterface;
use Vision2026\Domain\ValueObject\ArticleId;
use Vision2026\Domain\ValueObject\ArticleSlug;

// ============================================================================
// Load the article
// ============================================================================

$article = null;
$error = null;
$demoMode = DemoDataProvider::isEnabled();

if ($demoMode) {
    // ========================================================================
    // DEMO MODE: Use sample data
    // ========================================================================
    if (!empty($_GET['slug'])) {
        $article = DemoDataProvider::findBySlug($_GET['slug']);
    } elseif (!empty($_GET['id'])) {
        $article = DemoDataProvider::findById($_GET['id']);
    }

    if ($article === null) {
        $error = 'Article not found.';
    } elseif (!$article->isPublished()) {
        $error = 'This article is not available.';
        $article = null;
    }

} else {
    // ========================================================================
    // PRODUCTION MODE: Use real repository
    // ========================================================================
    try {
        $container = ServiceContainer::getInstance();
        $articleRepository = $container->get(ArticleRepositoryInterface::class);

        // Get article by slug or ID
        if (!empty($_GET['slug'])) {
            $slug = ArticleSlug::fromString($_GET['slug']);
            $article = $articleRepository->findBySlug($slug);
        } elseif (!empty($_GET['id'])) {
            $id = ArticleId::fromString($_GET['id']);
            $article = $articleRepository->find($id);
        }

        // Check if article exists and is published
        if ($article === null) {
            $error = 'Article not found.';
        } elseif (!$article->isPublished()) {
            $error = 'This article is not available.';
            $article = null;
        }

    } catch (\InvalidArgumentException $e) {
        $error = 'Invalid article identifier.';
    } catch (\Throwable $e) {
        $error = 'Unable to load article. The module may need configuration.';
        $debugError = $e->getMessage();
    }
}

// ============================================================================
// Prepare template data
// ============================================================================

// Prepare index URL with demo parameter if in demo mode
$indexUrl = XOOPS_URL . "/modules/{$moduleDirName}/index.php" . ($demoMode ? "?demo=1" : "");

// Assign common template variables
$xoopsTpl->assign('demoMode', $demoMode);
$xoopsTpl->assign('error', $error);
$xoopsTpl->assign('debugError', $debugError ?? null);
$xoopsTpl->assign('indexUrl', $indexUrl);
$xoopsTpl->assign('xoops_dirname', $moduleDirName);

if ($article !== null) {
    // Get author name from XOOPS
    $authorHandler = xoops_getHandler('user');
    $author = $authorHandler->get($article->getAuthorId()->value);
    $authorName = $author ? $author->getVar('uname') : 'Unknown';

    // Render content based on format
    if ($article->getContent()->format === 'markdown') {
        // Would use a Markdown parser here
        $contentRendered = nl2br(htmlspecialchars($article->getContent()->value));
    } else {
        // HTML content (sanitized)
        $contentRendered = $article->getContent()->value;
    }

    // Prepare article data for template
    $articleData = [
        'id'               => $article->id->toString(),
        'title'            => htmlspecialchars($article->getTitle()->value),
        'slug'             => $article->getSlug()->value,
        'content'          => $article->getContent()->value,
        'content_rendered' => $contentRendered,
        'content_format'   => $article->getContent()->format,
        'author_id'        => $article->getAuthorId()->value,
        'author_name'      => htmlspecialchars($authorName),
        'published_at'     => $article->getPublishedAt()?->format('F j, Y'),
        'reading_time'     => $article->getContent()->readingTime(),
        'word_count'       => number_format($article->getContent()->wordCount()),
    ];

    // Prepare share URLs
    $articleUrl = XOOPS_URL . "/modules/{$moduleDirName}/article.php?slug=" . $article->getSlug()->value;
    $shareTwitterUrl = 'https://twitter.com/intent/tweet?text=' . urlencode($article->getTitle()->value) . '&url=' . urlencode($articleUrl);
    $shareFacebookUrl = 'https://www.facebook.com/sharer/sharer.php?u=' . urlencode($articleUrl);

    // Assign article data to template
    $xoopsTpl->assign('article', $articleData);
    $xoopsTpl->assign('shareTwitterUrl', $shareTwitterUrl);
    $xoopsTpl->assign('shareFacebookUrl', $shareFacebookUrl);

    // Set page title
    $xoopsTpl->assign('xoops_pagetitle', $article->getTitle()->value . ' - ' . $xoopsModule->getVar('name'));
}

// ============================================================================
// Display template
// ============================================================================

$xoopsTpl->display('db:vision2026_article.tpl');

require_once XOOPS_ROOT_PATH . '/footer.php';
