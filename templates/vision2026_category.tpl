<div class="vision2026-category">
    <h1>📁 Category: <{$category.name}></h1>

    <{if $category.description}>
        <p style="color: #6c757d; font-size: 1.125rem; margin-bottom: 2rem;">
            <{$category.description}>
        </p>
    <{/if}>

    <{if $articles|@count == 0}>
        <div class="alert alert-warning" style="background: #fff3cd; border: 1px solid #ffeeba; padding: 1rem; border-radius: 4px; margin: 1rem 0;">
            No articles in this category yet.
        </div>
    <{else}>
        <div class="articles-grid" style="display: grid; gap: 1.5rem; margin: 2rem 0;">
            <{foreach item=article from=$articles}>
                <article class="article-card" style="background: white; border: 1px solid #dee2e6; border-radius: 8px; padding: 1.5rem;">
                    <h2 style="margin: 0 0 1rem 0;">
                        <a href="article.php?id=<{$article.id}>" style="color: #667eea; text-decoration: none;">
                            <{$article.title}>
                        </a>
                    </h2>

                    <div class="article-meta" style="color: #6c757d; font-size: 0.875rem; margin-bottom: 1rem;">
                        <span>📅 <{$article.publishedAt}></span>
                    </div>

                    <a href="article.php?id=<{$article.id}>" class="btn btn-primary" style="display: inline-block; padding: 0.5rem 1rem; background: #667eea; color: white; text-decoration: none; border-radius: 4px;">
                        Read More →
                    </a>
                </article>
            <{/foreach}>
        </div>
    <{/if}>

    <div style="margin-top: 2rem;">
        <a href="index.php" class="btn btn-secondary" style="display: inline-block; padding: 0.75rem 1.5rem; background: #6c757d; color: white; text-decoration: none; border-radius: 4px;">
            ← Back to All Articles
        </a>
    </div>
</div>

<style>
.vision2026-category {
    max-width: 1200px;
    margin: 0 auto;
    padding: 2rem 1rem;
}
</style>
