<?php

declare(strict_types=1);

/**
 * Vision 2026 Module - Admin Articles Management
 *
 * This demonstrates the Command/Handler pattern in action:
 * - Form submission triggers a Command
 * - Handler executes the use case
 * - Repository persists the result
 * - Events are dispatched for side effects
 */

use Xmf\Module\Admin;
use Vision2026\Infrastructure\Container\ServiceContainer;
use Vision2026\Domain\Repository\ArticleRepositoryInterface;
use Vision2026\Domain\ValueObject\ArticleId;
use Vision2026\Domain\ValueObject\ArticleTitle;
use Vision2026\Domain\ValueObject\ArticleContent;
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

// Get operation
$op = $_REQUEST['op'] ?? 'list';
$articleId = $_REQUEST['id'] ?? null;

// ============================================================================
// List Articles
// ============================================================================
if ($op === 'list') {
    $GLOBALS['adminObject']->displayNavigation('articles.php');

    // Get all articles
    $allArticles = [];
    try {
        foreach (['draft', 'published', 'archived'] as $status) {
            $statusEnum = \Vision2026\Domain\Entity\ArticleStatus::from($status);
            $articles = $articleRepository->findByStatus($statusEnum);
            $allArticles = array_merge($allArticles, $articles);
        }
    } catch (\Throwable $e) {
        $allArticles = [];
    }

    // Prepare data for template
    $articlesData = [];
    foreach ($allArticles as $article) {
        $articlesData[] = [
            'id' => $article->id->toString(),
            'title' => $article->getTitle()->value,
            'status' => $article->getStatus()->value,
            'created_at' => $article->getCreatedAt()->format('Y-m-d H:i'),
            'is_draft' => $article->isDraft(),
            'is_published' => $article->isPublished(),
        ];
    }

    // Assign to template
    $GLOBALS['xoopsTpl']->assign('articles', $articlesData);

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

    // Prepare article data for template
    $articleData = null;
    if ($article) {
        $articleData = [
            'id' => $article->id->toString(),
        ];
    }

    // Assign to template
    $GLOBALS['xoopsTpl']->assign('article', $articleData);
    $GLOBALS['xoopsTpl']->assign('title', $title);
    $GLOBALS['xoopsTpl']->assign('content', $content);
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
        $editId = $_POST['id'] ?? null;

        if ($editId) {
            // Update existing article
            $article = $articleRepository->findOrFail(ArticleId::fromString($editId));
            $article->update(
                ArticleTitle::fromString($title),
                ArticleContent::fromString($content),
                null
            );
            $articleRepository->save($article);
            redirect_header('articles.php', 2, _AM_VISION2026_SAVED);
        } else {
            // Create new article
            $command = new CreateArticle(
                $title,
                $content,
                $GLOBALS['xoopsUser']->uid()
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
