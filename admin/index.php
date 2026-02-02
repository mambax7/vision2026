<?php

declare(strict_types=1);

/**
 * Vision 2026 Module - Admin Dashboard
 *
 * This file demonstrates how the clean architecture integrates
 * with XOOPS admin interface. Notice how we:
 * 1. Bootstrap the module's autoloader
 * 2. Use the service container to get dependencies
 * 3. Keep the admin logic thin (delegating to services)
 */
use Xmf\Module\Admin;
use Vision2026\Infrastructure\Container\ServiceContainer;
use Vision2026\Domain\Repository\ArticleRepositoryInterface;
use Vision2026\Domain\Entity\ArticleStatus;

// XOOPS admin header
//require_once dirname(__DIR__, 3) . '/include/cp_header.php';
require_once __DIR__ . '/admin_header.php';
xoops_cp_header();

// Module autoloader
require_once dirname(__DIR__) . '/vendor/autoload.php';

// ============================================================================
// This is the Vision 2026 way: Use the container to get services
// ============================================================================

try {
    $container = ServiceContainer::getInstance();
    $articleRepository = $container->get(ArticleRepositoryInterface::class);

    // Get statistics using repository methods
    $stats = [
        'total'     => $articleRepository->count(),
        'published' => $articleRepository->countByStatus(ArticleStatus::Published),
        'drafts'    => $articleRepository->countByStatus(ArticleStatus::Draft),
        'archived'  => $articleRepository->countByStatus(ArticleStatus::Archived),
    ];

    $recentArticles = $articleRepository->findRecentPublished(5);

} catch (\Throwable $e) {
    // Fallback for when module isn't fully set up yet
    $stats = [
        'total'     => 0,
        'published' => 0,
        'drafts'    => 0,
        'archived'  => 0,
    ];
    $recentArticles = [];
    $setupMessage = 'Module not fully configured. Run composer install and check database tables.';
}

// ============================================================================
// Admin UI - Simple and clean
// ============================================================================

$GLOBALS['adminObject']->displayNavigation('index.php');

?>

<div class="vision2026-dashboard">
    <h2>📊 <?php echo _AM_VISION2026_DASHBOARD; ?></h2>

    <?php if (isset($setupMessage)): ?>
        <div class="alert alert-warning">
            <strong>Setup Required:</strong> <?php echo $setupMessage; ?>
        </div>
    <?php endif; ?>

    <!-- Statistics Cards -->
    <div class="stats-grid" style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 1rem; margin: 2rem 0;">
        <div class="stat-card" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 1.5rem; border-radius: 8px; text-align: center;">
            <div style="font-size: 2.5rem; font-weight: bold;"><?php echo $stats['total']; ?></div>
            <div style="opacity: 0.9;"><?php echo _AM_VISION2026_TOTAL_ARTICLES; ?></div>
        </div>
        <div class="stat-card" style="background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%); color: white; padding: 1.5rem; border-radius: 8px; text-align: center;">
            <div style="font-size: 2.5rem; font-weight: bold;"><?php echo $stats['published']; ?></div>
            <div style="opacity: 0.9;"><?php echo _AM_VISION2026_PUBLISHED_ARTICLES; ?></div>
        </div>
        <div class="stat-card" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); color: white; padding: 1.5rem; border-radius: 8px; text-align: center;">
            <div style="font-size: 2.5rem; font-weight: bold;"><?php echo $stats['drafts']; ?></div>
            <div style="opacity: 0.9;"><?php echo _AM_VISION2026_DRAFT_ARTICLES; ?></div>
        </div>
        <div class="stat-card" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); color: white; padding: 1.5rem; border-radius: 8px; text-align: center;">
            <div style="font-size: 2.5rem; font-weight: bold;"><?php echo $stats['archived']; ?></div>
            <div style="opacity: 0.9;"><?php echo _AM_VISION2026_ARCHIVED_ARTICLES; ?></div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="quick-actions" style="margin: 2rem 0;">
        <h3>⚡ Quick Actions</h3>
        <a href="articles.php?op=add" class="btn btn-primary" style="display: inline-block; padding: 0.75rem 1.5rem; background: #667eea; color: white; text-decoration: none; border-radius: 4px; margin-right: 0.5rem;">
            ➕ <?php echo _AM_VISION2026_ARTICLE_ADD; ?>
        </a>
        <a href="categories.php" class="btn btn-secondary" style="display: inline-block; padding: 0.75rem 1.5rem; background: #6c757d; color: white; text-decoration: none; border-radius: 4px;">
            📁 <?php echo _AM_VISION2026_CATEGORIES; ?>
        </a>
    </div>

    <!-- Architecture Info -->
    <div class="architecture-info" style="background: #f8f9fa; border-left: 4px solid #667eea; padding: 1.5rem; margin: 2rem 0; border-radius: 0 8px 8px 0;">
        <h3>🏗️ Vision 2026 Architecture</h3>
        <p>This module demonstrates XOOPS 2026 patterns:</p>
        <ul style="margin: 1rem 0; padding-left: 1.5rem;">
            <li><strong>Domain Layer</strong> - Pure business logic with entities, value objects, and domain events</li>
            <li><strong>Application Layer</strong> - Command/Query handlers orchestrating use cases</li>
            <li><strong>Infrastructure Layer</strong> - Database adapters, event dispatchers</li>
            <li><strong>Presentation Layer</strong> - Controllers and Smarty templates</li>
        </ul>
        <p style="margin-top: 1rem;">
            <a href="about.php" style="color: #667eea;">Learn more about the architecture →</a>
        </p>
    </div>

    <!-- Recent Articles -->
    <?php if (!empty($recentArticles)): ?>
    <div class="recent-articles" style="margin: 2rem 0;">
        <h3>📰 <?php echo _AM_VISION2026_RECENT_ACTIVITY; ?></h3>
        <table class="table" style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr style="background: #f8f9fa;">
                    <th style="padding: 0.75rem; text-align: left; border-bottom: 2px solid #dee2e6;"><?php echo _AM_VISION2026_TH_TITLE; ?></th>
                    <th style="padding: 0.75rem; text-align: left; border-bottom: 2px solid #dee2e6;"><?php echo _AM_VISION2026_TH_STATUS; ?></th>
                    <th style="padding: 0.75rem; text-align: left; border-bottom: 2px solid #dee2e6;"><?php echo _AM_VISION2026_TH_PUBLISHED; ?></th>
                    <th style="padding: 0.75rem; text-align: left; border-bottom: 2px solid #dee2e6;"><?php echo _AM_VISION2026_TH_ACTIONS; ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($recentArticles as $article): ?>
                <tr>
                    <td style="padding: 0.75rem; border-bottom: 1px solid #dee2e6;">
                        <?php echo htmlspecialchars($article->getTitle()->value); ?>
                    </td>
                    <td style="padding: 0.75rem; border-bottom: 1px solid #dee2e6;">
                        <span class="badge <?php echo $article->getStatus()->cssClass(); ?>">
                            <?php echo $article->getStatus()->label(); ?>
                        </span>
                    </td>
                    <td style="padding: 0.75rem; border-bottom: 1px solid #dee2e6;">
                        <?php echo $article->getPublishedAt()?->format('Y-m-d H:i') ?? '-'; ?>
                    </td>
                    <td style="padding: 0.75rem; border-bottom: 1px solid #dee2e6;">
                        <a href="articles.php?op=edit&id=<?php echo $article->id; ?>">Edit</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>

</div>

<style>
.vision2026-dashboard {
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, sans-serif;
}
.badge {
    display: inline-block;
    padding: 0.25rem 0.5rem;
    border-radius: 4px;
    font-size: 0.875rem;
}
.status-draft { background: #ffc107; color: #212529; }
.status-published { background: #28a745; color: white; }
.status-archived { background: #6c757d; color: white; }
</style>

<?php

require_once __DIR__ . '/admin_footer.php';
