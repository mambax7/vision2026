<?php

declare(strict_types=1);

/**
 * Vision 2026 Module - Frontend Category View
 *
 * Shows:
 * - List of all categories (when no category selected)
 * - Articles in a specific category (when category selected)
 *
 * URL patterns:
 * - category.php                    -> Show all categories
 * - category.php?id=5               -> Show articles in category ID 5
 * - category.php?slug=tutorials     -> Show articles in category by slug
 */

// XOOPS header
require_once dirname(__DIR__, 2) . '/mainfile.php';

// Set the template to use BEFORE including header.php
$GLOBALS['xoopsOption']['template_main'] = 'vision2026_category.tpl';

require_once XOOPS_ROOT_PATH . '/header.php';

// Module autoloader
$moduleDirName = basename(__DIR__);
require_once __DIR__ . '/vendor/autoload.php';

use Vision2026\Infrastructure\Container\ServiceContainer;
use Vision2026\Infrastructure\Demo\DemoDataProvider;
use Vision2026\Domain\Repository\ArticleRepositoryInterface;
use Vision2026\Domain\Repository\CategoryRepositoryInterface;
use Vision2026\Domain\ValueObject\CategoryId;
use Vision2026\Domain\ValueObject\CategorySlug;
use Vision2026\Domain\Entity\ArticleStatus;

// ============================================================================
// Get request parameters
// ============================================================================

$categoryId = isset($_GET['id']) ? (int)$_GET['id'] : null;
$categorySlug = $_GET['slug'] ?? null;
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = (int)($xoopsModuleConfig['articles_per_page'] ?? 10);
$demoMode = DemoDataProvider::isEnabled();

// ============================================================================
// Initialize variables
// ============================================================================

$categories = [];
$articles = [];
$currentCategory = null;
$total = 0;
$totalPages = 1;

// ============================================================================
// Demo Mode
// ============================================================================

if ($demoMode) {
    // In demo mode, we create some sample categories
    $sampleCategories = [
        ['id' => 1, 'name' => 'Tutorials', 'slug' => 'tutorials', 'description' => 'Step-by-step guides and how-tos', 'article_count' => 3],
        ['id' => 2, 'name' => 'Best Practices', 'slug' => 'best-practices', 'description' => 'Recommended approaches for PHP development', 'article_count' => 2],
        ['id' => 3, 'name' => 'Architecture', 'slug' => 'architecture', 'description' => 'Clean Architecture and Domain-Driven Design', 'article_count' => 1],
    ];

    if ($categoryId || $categorySlug) {
        // Find the current category
        foreach ($sampleCategories as $cat) {
            if (($categoryId && $cat['id'] == $categoryId) || ($categorySlug && $cat['slug'] == $categorySlug)) {
                $currentCategory = $cat;
                break;
            }
        }

        if ($currentCategory) {
            // Get demo articles and filter by "category"
            $demoArticles = DemoDataProvider::getPublishedArticles();
            foreach ($demoArticles as $article) {
                $articles[] = [
                    'id'           => $article->id->toString(),
                    'title'        => $article->getTitle()->value,
                    'slug'         => $article->getSlug()->value,
                    'excerpt'      => $article->getContent()->excerpt(200),
                    'published_at' => $article->getPublishedAt()?->format('F j, Y'),
                    'reading_time' => $article->getContent()->readingTime(),
                    'url'          => XOOPS_URL . "/modules/{$moduleDirName}/article.php?demo=1&slug=" . $article->getSlug()->value,
                ];
            }
            $total = count($articles);
        }
    } else {
        // Show all categories
        $categories = $sampleCategories;
    }
}

// ============================================================================
// Production Mode
// ============================================================================

else {
    try {
        $container = ServiceContainer::getInstance();
        $categoryRepository = $container->get(CategoryRepositoryInterface::class);
        $articleRepository = $container->get(ArticleRepositoryInterface::class);

        if ($categoryId || $categorySlug) {
            // Get specific category
            if ($categoryId) {
                $categoryEntity = $categoryRepository->find(CategoryId::fromInt($categoryId));
            } else {
                $categoryEntity = $categoryRepository->findBySlug(CategorySlug::fromString($categorySlug));
            }

            if ($categoryEntity) {
                $currentCategory = [
                    'id'          => $categoryEntity->id->value,
                    'name'        => $categoryEntity->getName()->value,
                    'slug'        => $categoryEntity->getSlug()->value,
                    'description' => $categoryEntity->getDescription(),
                ];

                // Get articles in this category
                $categoryArticles = $articleRepository->findByCategory($categoryEntity->id);

                // Filter to only published articles
                $publishedArticles = array_filter($categoryArticles, fn($a) => $a->isPublished());

                $total = count($publishedArticles);
                $totalPages = max(1, (int)ceil($total / $perPage));
                $page = min($page, $totalPages);
                $offset = ($page - 1) * $perPage;

                $paginatedArticles = array_slice($publishedArticles, $offset, $perPage);

                foreach ($paginatedArticles as $article) {
                    $articles[] = [
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
            }
        } else {
            // Get all categories with article counts
            $allCategories = $categoryRepository->findAll();

            // DEBUG: Uncomment to see what's happening
            // error_log("Found " . count($allCategories) . " categories");

            foreach ($allCategories as $categoryEntity) {
                $categoryArticles = $articleRepository->findByCategory($categoryEntity->id);

                // DEBUG: Uncomment to see article details
                // error_log("Category {$categoryEntity->id->value} ({$categoryEntity->getName()->value}): " . count($categoryArticles) . " total articles");
                // foreach ($categoryArticles as $a) {
                //     error_log("  - Article: {$a->getTitle()->value}, Status: {$a->getStatus()->value}, Published: " . ($a->isPublished() ? 'yes' : 'no'));
                // }

                $publishedCount = count(array_filter($categoryArticles, fn($a) => $a->isPublished()));

                $categories[] = [
                    'id'            => $categoryEntity->id->value,
                    'name'          => $categoryEntity->getName()->value,
                    'slug'          => $categoryEntity->getSlug()->value,
                    'description'   => $categoryEntity->getDescription(),
                    'article_count' => $publishedCount,
                ];
            }

            // Sort by weight (if available) or name
            usort($categories, fn($a, $b) => strcasecmp($a['name'], $b['name']));
        }

    } catch (\Throwable $e) {
        // Fallback for when infrastructure isn't ready
        $errorMessage = 'Unable to load categories. Please check module configuration.';
    }
}

// ============================================================================
// Assign to template
// ============================================================================

$xoopsTpl->assign('categories', $categories);
$xoopsTpl->assign('articles', $articles);
$xoopsTpl->assign('current_category', $currentCategory);
$xoopsTpl->assign('has_articles', !empty($articles));
$xoopsTpl->assign('has_categories', !empty($categories));
$xoopsTpl->assign('demo_mode', $demoMode);
$xoopsTpl->assign('error_message', $errorMessage ?? null);

// Pagination data
$xoopsTpl->assign('pagination', [
    'current_page' => $page,
    'total_pages'  => $totalPages,
    'total_items'  => $total,
    'per_page'     => $perPage,
    'has_prev'     => $page > 1,
    'has_next'     => $page < $totalPages,
]);

// URLs
$xoopsTpl->assign('base_url', XOOPS_URL . "/modules/{$moduleDirName}/category.php");
$xoopsTpl->assign('module_url', XOOPS_URL . "/modules/{$moduleDirName}");

// Page title
if ($currentCategory) {
    $xoopsTpl->assign('xoops_pagetitle', $currentCategory['name'] . ' - ' . ($xoopsModule->getVar('name') ?? 'Vision 2026'));
} else {
    $xoopsTpl->assign('xoops_pagetitle', (_MI_VISION2026_CATEGORIES ?? 'Categories') . ' - ' . ($xoopsModule->getVar('name') ?? 'Vision 2026'));
}

// ============================================================================
// Footer
// ============================================================================

require_once XOOPS_ROOT_PATH . '/footer.php';
