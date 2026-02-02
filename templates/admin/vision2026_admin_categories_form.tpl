<div class="vision2026-category-form">
    <h2><{if $category}>
        <{$smarty.const._AM_VISION2026_CATEGORY_EDIT}>
    <{else}>
        <{$smarty.const._AM_VISION2026_CATEGORY_ADD}>
    <{/if}></h2>

    <form method="post" action="categories.php" style="max-width: 800px;">
        <input type="hidden" name="op" value="save">
        <{$xoops_security_token}>
        <{if $category}>
            <input type="hidden" name="id" value="<{$category.id}>">
        <{/if}>

        <div style="margin-bottom: 1rem;">
            <label style="display: block; margin-bottom: 0.5rem; font-weight: bold;">
                <{$smarty.const._AM_VISION2026_CATEGORY_NAME}> <span style="color: red;">*</span>
            </label>
            <input type="text" name="name" value="<{$name|escape}>"
                   required style="width: 100%; padding: 0.5rem; border: 1px solid #ced4da; border-radius: 4px;">
        </div>

        <div style="margin-bottom: 1rem;">
            <label style="display: block; margin-bottom: 0.5rem; font-weight: bold;">
                <{$smarty.const._AM_VISION2026_CATEGORY_DESCRIPTION}>
            </label>
            <textarea name="description" rows="5"
                      style="width: 100%; padding: 0.5rem; border: 1px solid #ced4da; border-radius: 4px;"><{$description|escape}></textarea>
        </div>

        <div style="margin-bottom: 1rem;">
            <label style="display: block; margin-bottom: 0.5rem; font-weight: bold;">
                <{$smarty.const._AM_VISION2026_CATEGORY_WEIGHT}>
            </label>
            <input type="number" name="weight" value="<{$weight}>" min="0"
                   style="width: 200px; padding: 0.5rem; border: 1px solid #ced4da; border-radius: 4px;">
            <small style="display: block; color: #6c757d; margin-top: 0.25rem;">Lower numbers appear first</small>
        </div>

        <div style="margin-bottom: 1rem;">
            <button type="submit" class="btn btn-primary" style="padding: 0.75rem 1.5rem; background: #667eea; color: white; border: none; border-radius: 4px; cursor: pointer;">
                💾 <{$smarty.const._AM_VISION2026_ACTION_SAVE}>
            </button>
            <a href="categories.php" class="btn btn-secondary" style="display: inline-block; padding: 0.75rem 1.5rem; background: #6c757d; color: white; text-decoration: none; border-radius: 4px; margin-left: 0.5rem;">
                <{$smarty.const._AM_VISION2026_ACTION_CANCEL}>
            </a>
        </div>
    </form>
</div>
