<?php

declare(strict_types=1);

/**
 * Vision 2026 Module - Admin Articles Management
 *
 * Features:
 * - Category selection for articles
 * - Column sorting (title, status, category, date)
 * - Pagination with configurable items per page
 * - Category filter dropdown
 * - Command/Handler pattern for creating/updating articles
 */

use Xmf\Module\Admin;
use Vision2026\Infrastructure\Container\ServiceContainer;
use Vision2026\Domain\Repository\ArticleRepositoryInterface;
use Vision2026\Domain\Repository\CategoryRepositoryInterface;
use Vision2026\Domain\ValueObject\ArticleId;
use Vision2026\Domain\ValueObject\ArticleTitle;
use Vision2026\Domain\ValueObject\ArticleContent;
use Vision2026\Domain\ValueObject\CategoryId;
use Vision2026\Domain\Entity\ArticleStatus;
use Vision2026\Application\Command\CreateArticle;
use Vision2026\Application\Command\CreateArticleHandler;
use Vision2026\Application\Command\PublishArticle;
use Vision2026\Application\Command\PublishArticleHandler;

// XOOPS admin header
require_once __DIR__ . '/admin_header.php';
xoops_cp_header();

// Module autoloader
require_once dirname(__DIR__) . '/vendor/autoload.php';

$adminObject = Admin::getInstance();
$container = ServiceContainer::getInstance();
$articleRepository = $container->get(ArticleRepositoryInterface::class);
$categoryRepository = $container->get(CategoryRepositoryInterface::class);

// Get operation and parameters
$op = $_REQUEST['op'] ?? 'list';
$articleId = $_REQUEST['id'] ?? null;

// Pagination and sorting parameters
$page = max(1, (int)($_REQUEST['page'] ?? 1));
$perPage = max(5, min(100, (int)($_REQUEST['per_page'] ?? 10)));
$sortBy = $_REQUEST['sort'] ?? 'created_at';
$sortDir = strtoupper($_REQUEST['dir'] ?? 'DESC') === 'ASC' ? 'ASC' : 'DESC';
$filterCategory = $_REQUEST['category'] ?? '';
$filterStatus = $_REQUEST['status'] ?? '';

// Valid sort columns
$validSortColumns = ['title', 'status', 'category', 'created_at', 'published_at'];
if (!in_array($sortBy, $validSortColumns)) {
    $sortBy = 'created_at';
}

// ============================================================================
// Get all categories for filters and dropdowns
// ============================================================================
$allCategories = [];
try {
    $allCategories = $categoryRepository->findAll();
} catch (\Throwable $e) {
    $allCategories = [];
}

$categoriesMap = [];
foreach ($allCategories as $cat) {
    $categoriesMap[$cat->id->value] = $cat->getName()->value;
}

// ============================================================================
// List Articles
// ============================================================================
if ($op === 'list') {
    $GLOBALS['adminObject']->displayNavigation('articles.php');

    // Get all articles with filtering
    $allArticles = [];
    try {
        if ($filterCategory !== '' && $filterCategory !== '0') {
            // Filter by category
            $allArticles = $articleRepository->findByCategory(CategoryId::fromInt((int)$filterCategory));
        } elseif ($filterStatus !== '') {
            // Filter by status
            $statusEnum = ArticleStatus::from($filterStatus);
            $allArticles = $articleRepository->findByStatus($statusEnum);
        } else {
            // Get all articles
            foreach (['draft', 'published', 'archived'] as $status) {
                $statusEnum = ArticleStatus::from($status);
                $articles = $articleRepository->findByStatus($statusEnum);
                $allArticles = array_merge($allArticles, $articles);
            }
        }
    } catch (\Throwable $e) {
        $allArticles = [];
    }

    // Sort articles
    usort($allArticles, function($a, $b) use ($sortBy, $sortDir, $categoriesMap) {
        $valA = '';
        $valB = '';

        switch ($sortBy) {
            case 'title':
                $valA = strtolower($a->getTitle()->value);
                $valB = strtolower($b->getTitle()->value);
                break;
            case 'status':
                $valA = $a->getStatus()->value;
                $valB = $b->getStatus()->value;
                break;
            case 'category':
                $catIdA = $a->getCategoryId()?->value ?? 0;
                $catIdB = $b->getCategoryId()?->value ?? 0;
                $valA = strtolower($categoriesMap[$catIdA] ?? '');
                $valB = strtolower($categoriesMap[$catIdB] ?? '');
                break;
            case 'published_at':
                $valA = $a->getPublishedAt()?->getTimestamp() ?? 0;
                $valB = $b->getPublishedAt()?->getTimestamp() ?? 0;
                break;
            case 'created_at':
            default:
                $valA = $a->getCreatedAt()->getTimestamp();
                $valB = $b->getCreatedAt()->getTimestamp();
                break;
        }

        if ($sortDir === 'ASC') {
            return $valA <=> $valB;
        }
        return $valB <=> $valA;
    });

    // Pagination
    $total = count($allArticles);
    $totalPages = max(1, (int)ceil($total / $perPage));
    $page = min($page, $totalPages);
    $offset = ($page - 1) * $perPage;
    $paginatedArticles = array_slice($allArticles, $offset, $perPage);

    // Prepare data for template
    $articlesData = [];
    foreach ($paginatedArticles as $article) {
        $categoryId = $article->getCategoryId()?->value ?? null;
        $categoryName = $categoryId ? ($categoriesMap[$categoryId] ?? 'Unknown') : null;

        $articlesData[] = [
            'id' => $article->id->toString(),
            'title' => $article->getTitle()->value,
            'status' => $article->getStatus()->value,
            'status_label' => $article->getStatus()->label(),
            'status_css' => $article->getStatus()->cssClass(),
            'category_id' => $categoryId,
            'category_name' => $categoryName,
            'created_at' => $article->getCreatedAt()->format('Y-m-d H:i'),
            'published_at' => $article->getPublishedAt()?->format('Y-m-d H:i'),
            'is_draft' => $article->isDraft(),
            'is_published' => $article->isPublished(),
            'is_archived' => $article->isArchived(),
        ];
    }

    // Prepare categories for filter dropdown
    $categoriesData = [];
    foreach ($allCategories as $category) {
        $categoriesData[] = [
            'id' => $category->id->value,
            'name' => $category->getName()->value,
        ];
    }

    // Build base URL for sorting/pagination links
    $baseParams = [];
    if ($filterCategory !== '') $baseParams['category'] = $filterCategory;
    if ($filterStatus !== '') $baseParams['status'] = $filterStatus;
    if ($perPage != 10) $baseParams['per_page'] = $perPage;
    $baseUrl = 'articles.php?' . http_build_query($baseParams);

    // Assign to template
    $GLOBALS['xoopsTpl']->assign('articles', $articlesData);
    $GLOBALS['xoopsTpl']->assign('categories', $categoriesData);
    $GLOBALS['xoopsTpl']->assign('pagination', [
        'current_page' => $page,
        'total_pages' => $totalPages,
        'total_items' => $total,
        'per_page' => $perPage,
        'has_prev' => $page > 1,
        'has_next' => $page < $totalPages,
    ]);
    $GLOBALS['xoopsTpl']->assign('sort', [
        'column' => $sortBy,
        'direction' => $sortDir,
        'opposite' => $sortDir === 'ASC' ? 'DESC' : 'ASC',
    ]);
    $GLOBALS['xoopsTpl']->assign('filter', [
        'category' => $filterCategory,
        'status' => $filterStatus,
    ]);
    $GLOBALS['xoopsTpl']->assign('base_url', $baseUrl);

    // Display template
    $GLOBALS['xoopsTpl']->display('db:vision2026_admin_articles_list.tpl');
}

// ============================================================================
// Add/Edit Article Form
// ============================================================================
elseif ($op === 'add' || $op === 'edit') {
    $GLOBALS['adminObject']->displayNavigation('articles.php');

    $article = null;
    if ($op === 'edit' && $articleId) {
        try {
            $article = $articleRepository->find(ArticleId::fromString($articleId));
        } catch (\Throwable $e) {
            redirect_header('articles.php', 3, 'Article not found.');
            exit;
        }
    }

    $title = $article ? $article->getTitle()->value : '';
    $content = $article ? $article->getContent()->value : '';
    $selectedCategory = $article ? ($article->getCategoryId()?->value ?? '') : '';

    // Prepare article data for template
    $articleData = null;
    if ($article) {
        $articleData = [
            'id' => $article->id->toString(),
            'status' => $article->getStatus()->value,
            'status_label' => $article->getStatus()->label(),
        ];
    }

    // Prepare categories for dropdown
    $categoriesData = [];
    foreach ($allCategories as $category) {
        $categoriesData[] = [
            'id' => $category->id->value,
            'name' => $category->getName()->value,
            'selected' => ($category->id->value == $selectedCategory),
        ];
    }

    // Assign to template
    $GLOBALS['xoopsTpl']->assign('article', $articleData);
    $GLOBALS['xoopsTpl']->assign('title', $title);
    $GLOBALS['xoopsTpl']->assign('content', $content);
    $GLOBALS['xoopsTpl']->assign('categories', $categoriesData);
    $GLOBALS['xoopsTpl']->assign('selected_category', $selectedCategory);
    $GLOBALS['xoopsTpl']->assign('xoops_security_token', $GLOBALS['xoopsSecurity']->getTokenHTML());

    // Display template
    $GLOBALS['xoopsTpl']->display('db:vision2026_admin_articles_form.tpl');
}

// ============================================================================
// Save Article
// ============================================================================
elseif ($op === 'save' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!$GLOBALS['xoopsSecurity']->check()) {
        redirect_header('articles.php', 3, 'Security token validation failed.');
        exit;
    }

    try {
        $title = $_POST['title'] ?? '';
        $content = $_POST['content'] ?? '';
        $categoryId = !empty($_POST['category_id']) ? (int)$_POST['category_id'] : null;
        $editId = $_POST['id'] ?? null;

        if ($editId) {
            // Update existing article
            $article = $articleRepository->findOrFail(ArticleId::fromString($editId));
            $article->update(
                ArticleTitle::fromString($title),
                ArticleContent::fromString($content),
                $categoryId ? CategoryId::fromInt($categoryId) : null
            );
            $articleRepository->save($article);
            redirect_header('articles.php', 2, _AM_VISION2026_SAVED);
        } else {
            // Create new article using command/handler
            $command = new CreateArticle(
                $title,
                $content,
                $GLOBALS['xoopsUser']->uid(),
                $categoryId
            );

            $handler = $container->get(CreateArticleHandler::class);
            $article = $handler->handle($command);

            redirect_header('articles.php', 2, _AM_VISION2026_SAVED);
        }
    } catch (\Throwable $e) {
        redirect_header('articles.php', 3, _AM_VISION2026_ERROR_SAVE . ' ' . $e->getMessage());
    }
    exit;
}

// ============================================================================
// Publish Article
// ============================================================================
elseif ($op === 'publish' && $articleId) {
    try {
        $command = new PublishArticle($articleId);
        $handler = $container->get(PublishArticleHandler::class);
        $handler->handle($command);

        redirect_header('articles.php', 2, _AM_VISION2026_PUBLISHED);
    } catch (\Throwable $e) {
        redirect_header('articles.php', 3, 'Error: ' . $e->getMessage());
    }
    exit;
}

// ============================================================================
// Unpublish Article
// ============================================================================
elseif ($op === 'unpublish' && $articleId) {
    try {
        $article = $articleRepository->find(ArticleId::fromString($articleId));
        if (!$article) {
            redirect_header('articles.php', 3, 'Article not found.');
            exit;
        }

        $article->unpublish();
        $articleRepository->save($article);

        redirect_header('articles.php', 2, 'Article unpublished successfully.');
    } catch (\Throwable $e) {
        redirect_header('articles.php', 3, 'Error: ' . $e->getMessage());
    }
    exit;
}

// ============================================================================
// Archive Article
// ============================================================================
elseif ($op === 'archive' && $articleId) {
    try {
        $article = $articleRepository->find(ArticleId::fromString($articleId));
        if (!$article) {
            redirect_header('articles.php', 3, 'Article not found.');
            exit;
        }

        $article->archive();
        $articleRepository->save($article);

        redirect_header('articles.php', 2, 'Article archived successfully.');
    } catch (\Throwable $e) {
        redirect_header('articles.php', 3, 'Error: ' . $e->getMessage());
    }
    exit;
}

// ============================================================================
// Delete Article
// ============================================================================
elseif ($op === 'delete' && $articleId) {
    try {
        $article = $articleRepository->findOrFail(ArticleId::fromString($articleId));
        $articleRepository->remove($article);

        redirect_header('articles.php', 2, _AM_VISION2026_DELETED);
    } catch (\Throwable $e) {
        redirect_header('articles.php', 3, _AM_VISION2026_ERROR_DELETE);
    }
    exit;
}

require_once __DIR__ . '/admin_footer.php';
