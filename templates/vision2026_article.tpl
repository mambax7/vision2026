<link rel="stylesheet" href="<{$xoops_url}>/modules/<{$xoops_dirname}>/assets/css/styles.css">

<div class="vision2026-article">

    <{if $demoMode}>
    <div class="demo-banner">
        <div>
            <strong>🎭 Demo Mode</strong>
            <span style="opacity: 0.9; margin-left: 0.5rem;">This is sample content for demonstration</span>
        </div>
        <a href="<{$indexUrl}>">All Articles</a>
    </div>
    <{/if}>

    <{if $error}>
        <div class="error-message">
            <h2>😕 <{$error}></h2>
            <{if $debugError}>
                <p><small>Debug: <{$debugError}></small></p>
            <{/if}>
            <p>
                <a href="<{$indexUrl}>">← Back to Articles</a>
            </p>
        </div>

    <{else}>

        <article class="article-full">
            <!-- Header -->
            <header class="article-header">
                <h1>
                    <{$article.title}>
                </h1>
                <div class="meta">
                    <span>✍️ <{$article.author_name}></span>
                    <span>📅 <{$article.published_at}></span>
                    <span>⏱️ <{$article.reading_time}> min read</span>
                    <span>📝 <{$article.word_count}> words</span>
                </div>
            </header>

            <!-- Content -->
            <div class="article-content">
                <{$article.content_rendered}>
            </div>

            <!-- Footer -->
            <footer class="article-footer">
                <!-- Share buttons -->
                <div class="share-buttons">
                    <span>Share:</span>
                    <a href="<{$shareTwitterUrl}>"
                       target="_blank" rel="noopener"
                       class="share-twitter">
                        Twitter
                    </a>
                    <a href="<{$shareFacebookUrl}>"
                       target="_blank" rel="noopener"
                       class="share-facebook">
                        Facebook
                    </a>
                </div>

                <!-- Navigation -->
                <div class="navigation">
                    <a href="<{$indexUrl}>">
                        ← Back to Articles
                    </a>
                </div>
            </footer>
        </article>

        <!-- Architecture Note -->
        <aside class="architecture-note">
            <h4>🏗️ How This Page Works</h4>
            <p>This article was loaded using the Vision 2026 architecture:</p>
            <ol>
                <li><code>ArticleSlug::fromString()</code> validates the URL parameter</li>
                <li><code>ArticleRepository::findBySlug()</code> queries the database</li>
                <li><code>ArticleMapper::toDomain()</code> creates the domain entity</li>
                <li>The <code>Article</code> entity provides <code>getContent()->readingTime()</code></li>
            </ol>
        </aside>

    <{/if}>

</div>
