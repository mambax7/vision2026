<div class="vision2026-article-form">
    <h2><{if $article}>
        <{$smarty.const._AM_VISION2026_ARTICLE_EDIT}>
    <{else}>
        <{$smarty.const._AM_VISION2026_ARTICLE_ADD}>
    <{/if}></h2>

    <form method="post" action="articles.php" style="max-width: 800px;">
        <input type="hidden" name="op" value="save">
        <{$xoops_security_token}>
        <{if $article}>
            <input type="hidden" name="id" value="<{$article.id}>">
        <{/if}>

        <div style="margin-bottom: 1rem;">
            <label style="display: block; margin-bottom: 0.5rem; font-weight: bold;">
                <{$smarty.const._AM_VISION2026_ARTICLE_TITLE}> <span style="color: red;">*</span>
            </label>
            <input type="text" name="title" value="<{$title|escape}>"
                   required style="width: 100%; padding: 0.5rem; border: 1px solid #ced4da; border-radius: 4px;">
        </div>

        <div style="margin-bottom: 1rem;">
            <label style="display: block; margin-bottom: 0.5rem; font-weight: bold;">
                <{$smarty.const._AM_VISION2026_ARTICLE_CONTENT}> <span style="color: red;">*</span>
            </label>
            <textarea name="content" rows="15" required
                      style="width: 100%; padding: 0.5rem; border: 1px solid #ced4da; border-radius: 4px; font-family: monospace;"><{$content|escape}></textarea>
        </div>

        <div style="margin-bottom: 1rem;">
            <button type="submit" class="btn btn-primary" style="padding: 0.75rem 1.5rem; background: #667eea; color: white; border: none; border-radius: 4px; cursor: pointer;">
                💾 <{$smarty.const._AM_VISION2026_ACTION_SAVE}>
            </button>
            <a href="articles.php" class="btn btn-secondary" style="display: inline-block; padding: 0.75rem 1.5rem; background: #6c757d; color: white; text-decoration: none; border-radius: 4px; margin-left: 0.5rem;">
                <{$smarty.const._AM_VISION2026_ACTION_CANCEL}>
            </a>
        </div>
    </form>
</div>
