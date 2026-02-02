<div class="vision2026-categories">
    <h2>📁 <{$smarty.const._AM_VISION2026_CATEGORIES}></h2>

    <div style="margin: 1rem 0;">
        <a href="categories.php?op=add" class="btn btn-primary" style="display: inline-block; padding: 0.75rem 1.5rem; background: #667eea; color: white; text-decoration: none; border-radius: 4px;">
            ➕ <{$smarty.const._AM_VISION2026_CATEGORY_ADD}>
        </a>
    </div>

    <{if $categories|@count == 0}>
        <div class="alert alert-info">
            No categories yet. <a href="categories.php?op=add">Create your first category!</a>
        </div>
    <{else}>
        <table class="table" style="width: 100%; border-collapse: collapse; margin-top: 1rem;">
            <thead>
                <tr style="background: #f8f9fa;">
                    <th style="padding: 0.75rem; text-align: left; border-bottom: 2px solid #dee2e6;">ID</th>
                    <th style="padding: 0.75rem; text-align: left; border-bottom: 2px solid #dee2e6;"><{$smarty.const._AM_VISION2026_CATEGORY_NAME}></th>
                    <th style="padding: 0.75rem; text-align: left; border-bottom: 2px solid #dee2e6;"><{$smarty.const._AM_VISION2026_CATEGORY_SLUG}></th>
                    <th style="padding: 0.75rem; text-align: left; border-bottom: 2px solid #dee2e6;"><{$smarty.const._AM_VISION2026_CATEGORY_WEIGHT}></th>
                    <th style="padding: 0.75rem; text-align: left; border-bottom: 2px solid #dee2e6;"><{$smarty.const._AM_VISION2026_TH_ACTIONS}></th>
                </tr>
            </thead>
            <tbody>
                <{foreach item=category from=$categories}>
                <tr>
                    <td style="padding: 0.75rem; border-bottom: 1px solid #dee2e6;">
                        <{$category.id}>
                    </td>
                    <td style="padding: 0.75rem; border-bottom: 1px solid #dee2e6;">
                        <{$category.name|escape}>
                    </td>
                    <td style="padding: 0.75rem; border-bottom: 1px solid #dee2e6;">
                        <code><{$category.slug}></code>
                    </td>
                    <td style="padding: 0.75rem; border-bottom: 1px solid #dee2e6;">
                        <{$category.weight}>
                    </td>
                    <td style="padding: 0.75rem; border-bottom: 1px solid #dee2e6;">
                        <a href="categories.php?op=edit&id=<{$category.id}>" style="margin-right: 0.5rem;">✏️ Edit</a>
                        <a href="categories.php?op=delete&id=<{$category.id}>" onclick="return confirm('<{$smarty.const._AM_VISION2026_CONFIRM_DELETE|escape:"javascript"}>');" style="color: #dc3545;">🗑️ Delete</a>
                    </td>
                </tr>
                <{/foreach}>
            </tbody>
        </table>
    <{/if}>
</div>
