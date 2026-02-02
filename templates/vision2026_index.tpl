<link rel="stylesheet" href="<{$xoops_url}>/modules/<{$xoops_dirname}>/assets/css/styles.css">

<div class="vision2026-index">
    <{if $demoMode}>
    <div class="demo-banner" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 1rem 1.5rem; border-radius: 8px; margin-bottom: 1.5rem; display: flex; align-items: center; justify-content: space-between;">
        <div>
            <strong>🎭 Demo Mode Active</strong>
            <span style="opacity: 0.9; margin-left: 0.5rem;">Showing sample articles - no database required!</span>
        </div>
        <a href="?demo=0" style="color: white; background: rgba(255,255,255,0.2); padding: 0.25rem 0.75rem; border-radius: 4px; text-decoration: none; font-size: 0.875rem;">Exit Demo</a>
    </div>
    <{/if}>

    <header class="module-header" style="margin-bottom: 2rem; padding-bottom: 1rem; border-bottom: 2px solid #667eea;">
        <h1 style="color: #667eea; margin: 0;">📰 Vision 2026 Articles</h1>
        <p style="color: #666; margin: 0.5rem 0 0;">Demonstrating XOOPS 2026 Architecture</p>
    </header>

    <{if $setup_required}>
        <div class="setup-notice" style="background: #fff3cd; border: 1px solid #ffc107; padding: 1.5rem; border-radius: 8px; margin-bottom: 2rem;">
            <h3 style="margin-top: 0; color: #856404;">⚠️ Module Setup Required</h3>
            <p>The Vision 2026 module needs additional configuration:</p>
            <ol>
                <li>Run <code>composer install</code> in the module directory</li>
                <li>Ensure database tables are created</li>
                <li>Build the <code>XoopsArticleRepository</code> implementation</li>
            </ol>
            <{if $error_message}>
                <p><strong>Error:</strong> <code><{$error_message}></code></p>
            <{/if}>
        </div>

        <!-- Demo content to show what it will look like -->
        <h2>Preview: What Published Articles Will Look Like</h2>
        <div class="article-preview" style="background: #f8f9fa; padding: 1.5rem; border-radius: 8px; margin-bottom: 1rem;">
            <h3 style="margin-top: 0;">
                <a href="#" style="color: #333; text-decoration: none;">Getting Started with Clean Architecture</a>
            </h3>
            <div class="meta" style="color: #666; font-size: 0.9rem; margin-bottom: 0.75rem;">
                <span>📅 January 15, 2026</span>
                <span style="margin-left: 1rem;">⏱️ 5 min read</span>
            </div>
            <p style="margin-bottom: 0.75rem;">Learn how to structure your XOOPS modules using Clean Architecture principles. This guide covers domain-driven design, repository pattern, and command/query separation...</p>
            <a href="#" style="color: #667eea;">Read more →</a>
        </div>

    <{elseif !$has_articles}>
        <div class="no-articles" style="text-align: center; padding: 4rem 2rem; background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%); border-radius: 12px;">
            <div style="font-size: 4rem; margin-bottom: 1rem;">📝</div>
            <h2 style="color: #495057; margin: 0 0 1rem 0;">There are currently no articles</h2>
            <p style="color: #6c757d; font-size: 1.125rem; margin-bottom: 2rem;">Get started by creating your first article or check out our demo to see what Vision 2026 can do!</p>

            <div style="display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap;">
                <{if $xoops_isadmin}>
                <a href="<{$xoops_url}>/modules/<{$xoops_dirname}>/admin/articles.php?op=add"
                   style="display: inline-block; padding: 1rem 2rem; background: #667eea; color: white; text-decoration: none; border-radius: 8px; font-weight: 600; box-shadow: 0 2px 8px rgba(102, 126, 234, 0.3);">
                    ➕ Add New Article
                </a>
                <{/if}>
                <a href="<{$xoops_url}>/modules/<{$xoops_dirname}>/index.php?demo=1"
                   style="display: inline-block; padding: 1rem 2rem; background: #764ba2; color: white; text-decoration: none; border-radius: 8px; font-weight: 600; box-shadow: 0 2px 8px rgba(118, 75, 162, 0.3);">
                    🎭 Check Out Demo
                </a>
            </div>

            <div style="margin-top: 2rem; padding: 1.5rem; background: white; border-radius: 8px; text-align: left; max-width: 600px; margin-left: auto; margin-right: auto;">
                <h3 style="color: #667eea; margin-top: 0;">🏗️ What is Vision 2026?</h3>
                <p style="color: #495057; line-height: 1.6;">
                    Vision 2026 is a reference implementation demonstrating Clean Architecture and Domain-Driven Design
                    patterns for XOOPS CMS. It showcases modern PHP 8.1+ features including enums, readonly properties,
                    and strict typing.
                </p>
            </div>
        </div>

    <{else}>
        <div class="article-list">
            <{foreach item=article from=$articles}>
                <article class="article-card" style="background: #fff; padding: 1.5rem; border-radius: 8px; margin-bottom: 1.5rem; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                    <h2 style="margin-top: 0;">
                        <a href="<{$article.url}>" style="color: #333; text-decoration: none;">
                            <{$article.title}>
                        </a>
                    </h2>
                    <div class="meta" style="color: #666; font-size: 0.9rem; margin-bottom: 0.75rem;">
                        <span>📅 <{$article.published_at}></span>
                        <span style="margin-left: 1rem;">⏱️ <{$article.reading_time}> min read</span>
                    </div>
                    <p style="margin-bottom: 0.75rem;"><{$article.excerpt}></p>
                    <a href="<{$article.url}>" style="color: #667eea;">Read more →</a>
                </article>
            <{/foreach}>
        </div>

        <{if $total_pages > 1}>
            <nav class="pagination" style="display: flex; justify-content: center; gap: 0.5rem; margin-top: 2rem;">
                <{if $prev_url}>
                    <a href="<{$prev_url}>"
                       style="padding: 0.5rem 1rem; background: #f8f9fa; border-radius: 4px; text-decoration: none; color: #333;">
                        ← Previous
                    </a>
                <{/if}>

                <span style="padding: 0.5rem 1rem; color: #666;">
                    Page <{$current_page}> of <{$total_pages}>
                </span>

                <{if $next_url}>
                    <a href="<{$next_url}>"
                       style="padding: 0.5rem 1rem; background: #f8f9fa; border-radius: 4px; text-decoration: none; color: #333;">
                        Next →
                    </a>
                <{/if}>
            </nav>
        <{/if}>
    <{/if}>

    <!-- Architecture Info -->
    <aside class="architecture-note" style="margin-top: 3rem; padding: 1.5rem; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border-radius: 8px;">
        <h3 style="margin-top: 0;">🏗️ Powered by Clean Architecture</h3>
        <p style="margin-bottom: 0;">
            This page demonstrates the Vision 2026 pattern: Domain entities, Repository pattern, Value Objects, and Command handlers.
            <a href="<{$xoops_url}>/modules/<{$xoops_dirname}>/admin/about.php" style="color: #fff; text-decoration: underline;">Learn more →</a>
        </p>
    </aside>
</div>

<style>
.vision2026-index {
    max-width: 800px;
    margin: 0 auto;
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
}
.article-card:hover {
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}
.article-card h2 a:hover {
    color: #667eea;
}
</style>
