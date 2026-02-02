<?php

declare(strict_types=1);

/**
 * Vision 2026 Module - Frontend Index
 *
 * This demonstrates how clean architecture integrates with XOOPS frontend.
 *
 * Flow:
 * 1. XOOPS bootstrap loads
 * 2. We get the service container
 * 3. Query handler fetches published articles
 * 4. Data is passed to Smarty template
 *
 * Notice: Controllers would normally handle this, but for simplicity
 * we're keeping it in the entry file.
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
use Vision2026\Domain\Entity\ArticleStatus;

// ============================================================================
// Check for Demo Mode first
// ============================================================================

$demoMode = DemoDataProvider::isEnabled();
$page = max(1, (int) ($_GET['page'] ?? 1));
$perPage = 10;

if ($demoMode) {
    // ========================================================================
    // DEMO MODE: Use sample data - no database required!
    // ========================================================================
    $articles = DemoDataProvider::getPublishedArticles();
    $total = count($articles);
    $totalPages = 1;

    $articleList = [];
    foreach ($articles as $article) {
        $articleList[] = [
            'id'           => $article->id->toString(),
            'title'        => $article->getTitle()->value,
            'slug'         => $article->getSlug()->value,
            'excerpt'      => $article->getContent()->excerpt(200),
            'author_id'    => $article->getAuthorId()->value,
            'published_at' => $article->getPublishedAt()?->format('F j, Y'),
            'reading_time' => $article->getContent()->readingTime(),
            'url'          => XOOPS_URL . "/modules/{$moduleDirName}/article.php?demo=1&slug=" . $article->getSlug()->value,
        ];
    }
    $hasArticles = !empty($articleList);

} else {
    // ========================================================================
    // PRODUCTION MODE: Use real repository
    // ========================================================================
    try {
        $container = ServiceContainer::getInstance();
        $articleRepository = $container->get(ArticleRepositoryInterface::class);

        $perPage = (int) ($xoopsModuleConfig['articles_per_page'] ?? 10);

        // Fetch published articles with pagination
        $result = $articleRepository->findPublishedPaginated($page, $perPage);
        $articles = $result['items'];
        $total = $result['total'];
        $totalPages = (int) ceil($total / $perPage);

        // Transform domain objects to template-friendly arrays
        $articleList = [];
        foreach ($articles as $article) {
            $articleList[] = [
                'id'           => $article->id->toString(),
                'title'        => $article->getTitle()->value,
                'slug'         => $article->getSlug()->value,
                'excerpt'      => $article->getContent()->excerpt(200),
                'author_id'    => $article->getAuthorId()->value,
                'published_at' => $article->getPublishedAt()?->format('F j, Y'),
                'reading_time' => $article->getContent()->readingTime(),
                'url'          => XOOPS_URL . "/modules/{$moduleDirName}/article.php?slug=" . $article->getSlug()->value,
            ];
        }

        $hasArticles = !empty($articleList);

    } catch (\Throwable $e) {
        // Fallback for when infrastructure isn't ready
        $articleList = [];
        $hasArticles = false;
        $total = 0;
        $page = 1;
        $totalPages = 1;
        $setupRequired = true;
        $errorMessage = $e->getMessage();
    }
}

// ============================================================================
// Pass data to Smarty template
// ============================================================================

$xoopsTpl->assign('module_name', 'Vision 2026');
$xoopsTpl->assign('demoMode', $demoMode);

$xoopsTpl->assign('articles', $articleList);
$xoopsTpl->assign('has_articles', $hasArticles);
$xoopsTpl->assign('total_articles', $total ?? 0);
$xoopsTpl->assign('current_page', $page);
$xoopsTpl->assign('total_pages', $totalPages);
$xoopsTpl->assign('setup_required', $setupRequired ?? false);
$xoopsTpl->assign('error_message', $errorMessage ?? '');

// Pagination
if ($totalPages > 1) {
    $pagination = [];
    for ($i = 1; $i <= $totalPages; $i++) {
        $pagination[] = [
            'page' => $i,
            'url'  => XOOPS_URL . "/modules/{$moduleDirName}/index.php?page={$i}",
            'current' => ($i === $page),
        ];
    }
    $xoopsTpl->assign('pagination', $pagination);
    $xoopsTpl->assign('prev_url', $page > 1 ? XOOPS_URL . "/modules/{$moduleDirName}/index.php?page=" . ($page - 1) : '');
    $xoopsTpl->assign('next_url', $page < $totalPages ? XOOPS_URL . "/modules/{$moduleDirName}/index.php?page=" . ($page + 1) : '');
}

// ============================================================================
// Display Smarty Template
// ============================================================================

$xoopsTpl->display('db:vision2026_index.tpl');

require_once XOOPS_ROOT_PATH . '/footer.php';
