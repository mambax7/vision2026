<div class="vision2026-articles">
    <h2>📝 <{$smarty.const._AM_VISION2026_ARTICLES}></h2>

    <div style="margin: 1rem 0;">
        <a href="articles.php?op=add" class="btn btn-primary" style="display: inline-block; padding: 0.75rem 1.5rem; background: #667eea; color: white; text-decoration: none; border-radius: 4px;">
            ➕ <{$smarty.const._AM_VISION2026_ARTICLE_ADD}>
        </a>
    </div>

    <{if $articles|@count == 0}>
        <div class="alert alert-info">
            No articles yet. <a href="articles.php?op=add">Create your first article!</a>
        </div>
    <{else}>
        <table class="table" style="width: 100%; border-collapse: collapse; margin-top: 1rem;">
            <thead>
                <tr style="background: #f8f9fa;">
                    <th style="padding: 0.75rem; text-align: left; border-bottom: 2px solid #dee2e6;"><{$smarty.const._AM_VISION2026_TH_TITLE}></th>
                    <th style="padding: 0.75rem; text-align: left; border-bottom: 2px solid #dee2e6;"><{$smarty.const._AM_VISION2026_TH_STATUS}></th>
                    <th style="padding: 0.75rem; text-align: left; border-bottom: 2px solid #dee2e6;"><{$smarty.const._AM_VISION2026_TH_CREATED}></th>
                    <th style="padding: 0.75rem; text-align: left; border-bottom: 2px solid #dee2e6;"><{$smarty.const._AM_VISION2026_TH_ACTIONS}></th>
                </tr>
            </thead>
            <tbody>
                <{foreach item=article from=$articles}>
                <tr>
                    <td style="padding: 0.75rem; border-bottom: 1px solid #dee2e6;">
                        <{$article.title|escape}>
                    </td>
                    <td style="padding: 0.75rem; border-bottom: 1px solid #dee2e6;">
                        <span class="badge status-<{$article.status|lower}>" style="padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.875rem;">
                            <{$article.status|capitalize}>
                        </span>
                    </td>
                    <td style="padding: 0.75rem; border-bottom: 1px solid #dee2e6;">
                        <{$article.created_at}>
                    </td>
                    <td style="padding: 0.75rem; border-bottom: 1px solid #dee2e6;">
                        <a href="articles.php?op=edit&id=<{$article.id}>" style="margin-right: 0.5rem;">✏️ Edit</a>
                        <{if $article.is_draft}>
                            <a href="articles.php?op=publish&id=<{$article.id}>" style="margin-right: 0.5rem; color: #28a745;">✅ Publish</a>
                        <{/if}>
                        <{if $article.is_published}>
                            <a href="articles.php?op=unpublish&id=<{$article.id}>" onclick="return confirm('Unpublish this article? It will no longer be visible to the public.');" style="margin-right: 0.5rem; color: #ffc107;">📝 Unpublish</a>
                        <{/if}>
                        <a href="articles.php?op=delete&id=<{$article.id}>" onclick="return confirm('<{$smarty.const._AM_VISION2026_CONFIRM_DELETE|escape:"javascript"}>');" style="color: #dc3545;">🗑️ Delete</a>
                    </td>
                </tr>
                <{/foreach}>
            </tbody>
        </table>
    <{/if}>
</div>

<style>
.status-draft { background: #ffc107; color: #212529; }
.status-published { background: #28a745; color: white; }
.status-archived { background: #6c757d; color: white; }
</style>
