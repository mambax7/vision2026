<div class="vision2026-category">

    <{if $demo_mode}>
    <div class="demo-banner" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 1rem; border-radius: 8px; margin-bottom: 2rem; text-align: center;">
        🎭 <strong>Demo Mode</strong> - Sample data for demonstration. No database required!
    </div>
    <{/if}>

    <{if $error_message}>
    <div class="alert alert-danger" style="background: #f8d7da; border: 1px solid #f5c6cb; padding: 1rem; border-radius: 4px; color: #721c24; margin-bottom: 1rem;">
        <{$error_message}>
    </div>
    <{/if}>

    <{* ========================================================================= *}>
    <{* SHOW SINGLE CATEGORY WITH ITS ARTICLES                                    *}>
    <{* ========================================================================= *}>

    <{if $current_category}>
        <nav aria-label="breadcrumb" style="margin-bottom: 1.5rem;">
            <a href="<{$module_url}>/index.php" style="color: #667eea; text-decoration: none;">Home</a>
            <span style="color: #6c757d; margin: 0 0.5rem;">›</span>
            <a href="<{$base_url}>" style="color: #667eea; text-decoration: none;">Categories</a>
            <span style="color: #6c757d; margin: 0 0.5rem;">›</span>
            <span style="color: #6c757d;"><{$current_category.name|escape}></span>
        </nav>

        <h1 style="margin: 0 0 0.5rem 0;">📁 <{$current_category.name|escape}></h1>

        <{if $current_category.description}>
            <p style="color: #6c757d; font-size: 1.125rem; margin-bottom: 2rem;">
                <{$current_category.description|escape}>
            </p>
        <{/if}>

        <{if !$has_articles}>
            <div class="alert alert-info" style="background: #d1ecf1; border: 1px solid #bee5eb; padding: 1.5rem; border-radius: 4px; color: #0c5460; margin: 2rem 0;">
                <strong>No articles yet.</strong><br>
                There are no published articles in this category.
            </div>
        <{else}>
            <p style="color: #6c757d; margin-bottom: 1rem;">
                <{$pagination.total_items}> article<{if $pagination.total_items != 1}>s<{/if}> in this category
            </p>

            <div class="articles-grid" style="display: grid; gap: 1.5rem; margin: 2rem 0;">
                <{foreach item=article from=$articles}>
                    <article class="article-card" style="background: white; border: 1px solid #dee2e6; border-radius: 8px; padding: 1.5rem; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
                        <h2 style="margin: 0 0 0.75rem 0; font-size: 1.25rem;">
                            <a href="<{$article.url}>" style="color: #343a40; text-decoration: none;">
                                <{$article.title|escape}>
                            </a>
                        </h2>

                        <div class="article-meta" style="color: #6c757d; font-size: 0.875rem; margin-bottom: 1rem;">
                            <{if $article.published_at}>
                                <span>📅 <{$article.published_at}></span>
                            <{/if}>
                            <{if $article.reading_time}>
                                <span style="margin-left: 1rem;">⏱️ <{$article.reading_time}> min read</span>
                            <{/if}>
                        </div>

                        <{if $article.excerpt}>
                            <p style="color: #495057; margin-bottom: 1rem; line-height: 1.6;">
                                <{$article.excerpt|escape}>
                            </p>
                        <{/if}>

                        <a href="<{$article.url}>" class="read-more" style="display: inline-block; padding: 0.5rem 1rem; background: #667eea; color: white; text-decoration: none; border-radius: 4px; font-size: 0.875rem;">
                            Read Article →
                        </a>
                    </article>
                <{/foreach}>
            </div>

            <{* Pagination *}>
            <{if $pagination.total_pages > 1}>
            <div class="pagination" style="display: flex; justify-content: center; gap: 0.25rem; margin: 2rem 0;">
                <{if $pagination.has_prev}>
                    <a href="<{$base_url}>?id=<{$current_category.id}>&page=1" style="padding: 0.5rem 0.75rem; border: 1px solid #dee2e6; border-radius: 4px; text-decoration: none; color: #495057;">«</a>
                    <a href="<{$base_url}>?id=<{$current_category.id}>&page=<{$pagination.current_page - 1}>" style="padding: 0.5rem 0.75rem; border: 1px solid #dee2e6; border-radius: 4px; text-decoration: none; color: #495057;">‹ Prev</a>
                <{/if}>

                <span style="padding: 0.5rem 1rem; background: #667eea; color: white; border-radius: 4px;">
                    Page <{$pagination.current_page}> of <{$pagination.total_pages}>
                </span>

                <{if $pagination.has_next}>
                    <a href="<{$base_url}>?id=<{$current_category.id}>&page=<{$pagination.current_page + 1}>" style="padding: 0.5rem 0.75rem; border: 1px solid #dee2e6; border-radius: 4px; text-decoration: none; color: #495057;">Next ›</a>
                    <a href="<{$base_url}>?id=<{$current_category.id}>&page=<{$pagination.total_pages}>" style="padding: 0.5rem 0.75rem; border: 1px solid #dee2e6; border-radius: 4px; text-decoration: none; color: #495057;">»</a>
                <{/if}>
            </div>
            <{/if}>
        <{/if}>

        <div style="margin-top: 2rem; padding-top: 1rem; border-top: 1px solid #dee2e6;">
            <a href="<{$base_url}>" style="display: inline-block; padding: 0.75rem 1.5rem; background: #6c757d; color: white; text-decoration: none; border-radius: 4px;">
                ← Back to Categories
            </a>
            <a href="<{$module_url}>/index.php" style="display: inline-block; padding: 0.75rem 1.5rem; background: #f8f9fa; color: #495057; text-decoration: none; border-radius: 4px; margin-left: 0.5rem; border: 1px solid #dee2e6;">
                All Articles
            </a>
        </div>

    <{* ========================================================================= *}>
    <{* SHOW ALL CATEGORIES LIST                                                  *}>
    <{* ========================================================================= *}>

    <{else}>
        <h1 style="margin: 0 0 1rem 0;">📁 Categories</h1>
        <p style="color: #6c757d; font-size: 1.125rem; margin-bottom: 2rem;">
            Browse articles by category
        </p>

        <{if !$has_categories}>
            <div class="alert alert-info" style="background: #d1ecf1; border: 1px solid #bee5eb; padding: 1.5rem; border-radius: 4px; color: #0c5460;">
                <strong>No categories yet.</strong><br>
                Categories will appear here once they are created.
            </div>
        <{else}>
            <div class="categories-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 1.5rem; margin: 2rem 0;">
                <{foreach item=category from=$categories}>
                    <a href="<{$base_url}>?id=<{$category.id}>" class="category-card" style="display: block; background: white; border: 1px solid #dee2e6; border-radius: 8px; padding: 1.5rem; text-decoration: none; transition: all 0.2s; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
                        <h2 style="margin: 0 0 0.5rem 0; color: #343a40; font-size: 1.25rem;">
                            📁 <{$category.name|escape}>
                        </h2>
                        <{if $category.description}>
                            <p style="color: #6c757d; margin: 0 0 1rem 0; font-size: 0.9375rem; line-height: 1.5;">
                                <{$category.description|escape|truncate:120:"..."}>
                            </p>
                        <{/if}>
                        <span class="article-count" style="display: inline-block; background: #667eea; color: white; padding: 0.25rem 0.75rem; border-radius: 20px; font-size: 0.8125rem;">
                            <{$category.article_count}> article<{if $category.article_count != 1}>s<{/if}>
                        </span>
                    </a>
                <{/foreach}>
            </div>
        <{/if}>

        <div style="margin-top: 2rem; padding-top: 1rem; border-top: 1px solid #dee2e6;">
            <a href="<{$module_url}>/index.php" style="display: inline-block; padding: 0.75rem 1.5rem; background: #667eea; color: white; text-decoration: none; border-radius: 4px;">
                ← All Articles
            </a>
        </div>
    <{/if}>

</div>

<style>
.vision2026-category {
    max-width: 1200px;
    margin: 0 auto;
    padding: 2rem 1rem;
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, sans-serif;
}

.category-card:hover {
    border-color: #667eea;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.15);
}

.article-card:hover {
    border-color: #667eea;
}

.article-card h2 a:hover {
    color: #667eea;
}

.read-more:hover {
    background: #5a67d8;
}
</style>
