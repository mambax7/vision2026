<div class="vision2026-articles">
    <h2>📝 <{$smarty.const._AM_VISION2026_ARTICLES}></h2>

    <div style="margin: 1rem 0; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
        <a href="articles.php?op=add" class="btn btn-primary" style="display: inline-block; padding: 0.75rem 1.5rem; background: #667eea; color: white; text-decoration: none; border-radius: 4px;">
            ➕ <{$smarty.const._AM_VISION2026_ARTICLE_ADD}>
        </a>

        <!-- Filters -->
        <form method="get" action="articles.php" style="display: flex; gap: 0.5rem; align-items: center;">
            <select name="category" onchange="this.form.submit()" style="padding: 0.5rem; border: 1px solid #ced4da; border-radius: 4px;">
                <option value="">All Categories</option>
                <{foreach item=category from=$categories}>
                    <option value="<{$category.id}>" <{if $filter.category == $category.id}>selected<{/if}>><{$category.name|escape}></option>
                <{/foreach}>
            </select>
            <select name="status" onchange="this.form.submit()" style="padding: 0.5rem; border: 1px solid #ced4da; border-radius: 4px;">
                <option value="">All Status</option>
                <option value="draft" <{if $filter.status == 'draft'}>selected<{/if}>>Draft</option>
                <option value="published" <{if $filter.status == 'published'}>selected<{/if}>>Published</option>
                <option value="archived" <{if $filter.status == 'archived'}>selected<{/if}>>Archived</option>
            </select>
            <select name="per_page" onchange="this.form.submit()" style="padding: 0.5rem; border: 1px solid #ced4da; border-radius: 4px;">
                <option value="10" <{if $pagination.per_page == 10}>selected<{/if}>>10 per page</option>
                <option value="25" <{if $pagination.per_page == 25}>selected<{/if}>>25 per page</option>
                <option value="50" <{if $pagination.per_page == 50}>selected<{/if}>>50 per page</option>
            </select>
            <{if $filter.category || $filter.status}>
                <a href="articles.php" style="padding: 0.5rem 1rem; background: #f8f9fa; border: 1px solid #ced4da; border-radius: 4px; text-decoration: none; color: #495057;">Clear</a>
            <{/if}>
        </form>
    </div>

    <{if $articles|@count == 0}>
        <div class="alert alert-info" style="padding: 1rem; background: #d1ecf1; border: 1px solid #bee5eb; border-radius: 4px; color: #0c5460;">
            <{if $filter.category || $filter.status}>
                No articles match the current filters. <a href="articles.php">Clear filters</a> or <a href="articles.php?op=add">create a new article</a>.
            <{else}>
                No articles yet. <a href="articles.php?op=add">Create your first article!</a>
            <{/if}>
        </div>
    <{else}>
        <!-- Results summary -->
        <div style="margin-bottom: 0.5rem; color: #6c757d; font-size: 0.875rem;">
            Showing <{$pagination.current_page * $pagination.per_page - $pagination.per_page + 1}>-<{if $pagination.current_page == $pagination.total_pages}><{$pagination.total_items}><{else}><{$pagination.current_page * $pagination.per_page}><{/if}> of <{$pagination.total_items}> articles
        </div>

        <table class="table" style="width: 100%; border-collapse: collapse; margin-top: 0.5rem;">
            <thead>
                <tr style="background: #f8f9fa;">
                    <th style="padding: 0.75rem; text-align: left; border-bottom: 2px solid #dee2e6;">
                        <a href="<{$base_url}>&sort=title&dir=<{if $sort.column == 'title'}><{$sort.opposite}><{else}>ASC<{/if}>" style="text-decoration: none; color: inherit; display: flex; align-items: center; gap: 0.25rem;">
                            <{$smarty.const._AM_VISION2026_TH_TITLE}>
                            <{if $sort.column == 'title'}>
                                <span style="font-size: 0.75rem;"><{if $sort.direction == 'ASC'}>▲<{else}>▼<{/if}></span>
                            <{/if}>
                        </a>
                    </th>
                    <th style="padding: 0.75rem; text-align: left; border-bottom: 2px solid #dee2e6;">
                        <a href="<{$base_url}>&sort=category&dir=<{if $sort.column == 'category'}><{$sort.opposite}><{else}>ASC<{/if}>" style="text-decoration: none; color: inherit; display: flex; align-items: center; gap: 0.25rem;">
                            <{$smarty.const._AM_VISION2026_TH_CATEGORY|default:'Category'}>
                            <{if $sort.column == 'category'}>
                                <span style="font-size: 0.75rem;"><{if $sort.direction == 'ASC'}>▲<{else}>▼<{/if}></span>
                            <{/if}>
                        </a>
                    </th>
                    <th style="padding: 0.75rem; text-align: left; border-bottom: 2px solid #dee2e6;">
                        <a href="<{$base_url}>&sort=status&dir=<{if $sort.column == 'status'}><{$sort.opposite}><{else}>ASC<{/if}>" style="text-decoration: none; color: inherit; display: flex; align-items: center; gap: 0.25rem;">
                            <{$smarty.const._AM_VISION2026_TH_STATUS}>
                            <{if $sort.column == 'status'}>
                                <span style="font-size: 0.75rem;"><{if $sort.direction == 'ASC'}>▲<{else}>▼<{/if}></span>
                            <{/if}>
                        </a>
                    </th>
                    <th style="padding: 0.75rem; text-align: left; border-bottom: 2px solid #dee2e6;">
                        <a href="<{$base_url}>&sort=created_at&dir=<{if $sort.column == 'created_at'}><{$sort.opposite}><{else}>DESC<{/if}>" style="text-decoration: none; color: inherit; display: flex; align-items: center; gap: 0.25rem;">
                            <{$smarty.const._AM_VISION2026_TH_CREATED}>
                            <{if $sort.column == 'created_at'}>
                                <span style="font-size: 0.75rem;"><{if $sort.direction == 'ASC'}>▲<{else}>▼<{/if}></span>
                            <{/if}>
                        </a>
                    </th>
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
                        <{if $article.category_id}>
                            <a href="articles.php?category=<{$article.category_id}>" style="color: #667eea; text-decoration: none;">
                                📁 <{$article.category_name|escape}>
                            </a>
                        <{else}>
                            <span style="color: #adb5bd;">—</span>
                        <{/if}>
                    </td>
                    <td style="padding: 0.75rem; border-bottom: 1px solid #dee2e6;">
                        <span class="badge <{$article.status_css}>" style="padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.875rem;">
                            <{$article.status_label}>
                        </span>
                    </td>
                    <td style="padding: 0.75rem; border-bottom: 1px solid #dee2e6;">
                        <{$article.created_at}>
                    </td>
                    <td style="padding: 0.75rem; border-bottom: 1px solid #dee2e6;">
                        <a href="articles.php?op=edit&id=<{$article.id}>" style="margin-right: 0.5rem;" title="Edit">✏️</a>
                        <{if $article.is_draft}>
                            <a href="articles.php?op=publish&id=<{$article.id}>" style="margin-right: 0.5rem; color: #28a745;" title="Publish">✅</a>
                        <{/if}>
                        <{if $article.is_published}>
                            <a href="articles.php?op=unpublish&id=<{$article.id}>" onclick="return confirm('Unpublish this article?');" style="margin-right: 0.5rem; color: #ffc107;" title="Unpublish">📝</a>
                        <{/if}>
                        <{if !$article.is_archived}>
                            <a href="articles.php?op=archive&id=<{$article.id}>" onclick="return confirm('Archive this article?');" style="margin-right: 0.5rem; color: #6c757d;" title="Archive">📦</a>
                        <{/if}>
                        <a href="articles.php?op=delete&id=<{$article.id}>" onclick="return confirm('<{$smarty.const._AM_VISION2026_CONFIRM_DELETE|escape:"javascript"}>');" style="color: #dc3545;" title="Delete">🗑️</a>
                    </td>
                </tr>
                <{/foreach}>
            </tbody>
        </table>

        <!-- Pagination -->
        <{if $pagination.total_pages > 1}>
        <div style="margin-top: 1rem; display: flex; justify-content: center; gap: 0.25rem;">
            <{if $pagination.has_prev}>
                <a href="<{$base_url}>&sort=<{$sort.column}>&dir=<{$sort.direction}>&page=1" style="padding: 0.5rem 0.75rem; border: 1px solid #dee2e6; border-radius: 4px; text-decoration: none; color: #495057;">«</a>
                <a href="<{$base_url}>&sort=<{$sort.column}>&dir=<{$sort.direction}>&page=<{$pagination.current_page - 1}>" style="padding: 0.5rem 0.75rem; border: 1px solid #dee2e6; border-radius: 4px; text-decoration: none; color: #495057;">‹</a>
            <{/if}>

            <{section name=pages start=1 loop=$pagination.total_pages+1}>
                <{if $smarty.section.pages.index == $pagination.current_page}>
                    <span style="padding: 0.5rem 0.75rem; background: #667eea; color: white; border-radius: 4px;"><{$smarty.section.pages.index}></span>
                <{elseif $smarty.section.pages.index >= $pagination.current_page - 2 && $smarty.section.pages.index <= $pagination.current_page + 2}>
                    <a href="<{$base_url}>&sort=<{$sort.column}>&dir=<{$sort.direction}>&page=<{$smarty.section.pages.index}>" style="padding: 0.5rem 0.75rem; border: 1px solid #dee2e6; border-radius: 4px; text-decoration: none; color: #495057;"><{$smarty.section.pages.index}></a>
                <{/if}>
            <{/section}>

            <{if $pagination.has_next}>
                <a href="<{$base_url}>&sort=<{$sort.column}>&dir=<{$sort.direction}>&page=<{$pagination.current_page + 1}>" style="padding: 0.5rem 0.75rem; border: 1px solid #dee2e6; border-radius: 4px; text-decoration: none; color: #495057;">›</a>
                <a href="<{$base_url}>&sort=<{$sort.column}>&dir=<{$sort.direction}>&page=<{$pagination.total_pages}>" style="padding: 0.5rem 0.75rem; border: 1px solid #dee2e6; border-radius: 4px; text-decoration: none; color: #495057;">»</a>
            <{/if}>
        </div>
        <{/if}>
    <{/if}>
</div>

<style>
.status-draft { background: #ffc107; color: #212529; }
.status-published { background: #28a745; color: white; }
.status-archived { background: #6c757d; color: white; }
</style>
