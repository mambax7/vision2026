<?php

declare(strict_types=1);

/**
 * Vision 2026 Module - Admin Categories Management
 */

use Xmf\Module\Admin;
use Vision2026\Infrastructure\Container\ServiceContainer;
use Vision2026\Domain\Repository\CategoryRepositoryInterface;
use Vision2026\Domain\ValueObject\CategoryId;
use Vision2026\Domain\ValueObject\CategoryName;
use Vision2026\Domain\ValueObject\CategorySlug;
use Vision2026\Domain\Entity\Category;

// XOOPS admin header
require_once __DIR__ . '/admin_header.php';
xoops_cp_header();

// Module autoloader
require_once dirname(__DIR__) . '/vendor/autoload.php';

$adminObject = Admin::getInstance();
$container = ServiceContainer::getInstance();
$categoryRepository = $container->get(CategoryRepositoryInterface::class);

// Get operation
$op = $_REQUEST['op'] ?? 'list';
$categoryId = $_REQUEST['id'] ?? null;

// ============================================================================
// List Categories
// ============================================================================
if ($op === 'list') {
    $GLOBALS['adminObject']->displayNavigation('categories.php');

    // Get all categories
    $allCategories = [];
    try {
        $allCategories = $categoryRepository->findAll();
    } catch (\Throwable $e) {
        $allCategories = [];
    }

    // Prepare data for template
    $categoriesData = [];
    foreach ($allCategories as $category) {
        $categoriesData[] = [
            'id' => $category->id->value,
            'name' => $category->getName()->value,
            'slug' => $category->getSlug()->value,
            'description' => $category->getDescription(),
            'weight' => $category->getWeight(),
        ];
    }

    // Assign to template
    $GLOBALS['xoopsTpl']->assign('categories', $categoriesData);

    // Display template
    $GLOBALS['xoopsTpl']->display('db:vision2026_admin_categories_list.tpl');
}

// ============================================================================
// Add/Edit Category Form
// ============================================================================
elseif ($op === 'add' || $op === 'edit') {
    $GLOBALS['adminObject']->displayNavigation('categories.php');

    $category = null;
    if ($op === 'edit' && $categoryId) {
        try {
            $category = $categoryRepository->find(CategoryId::fromInt((int)$categoryId));
        } catch (\Throwable $e) {
            redirect_header('categories.php', 3, 'Category not found.');
            exit;
        }
    }

    $name = $category ? $category->getName()->value : '';
    $description = $category ? $category->getDescription() : '';
    $weight = $category ? $category->getWeight() : 0;

    // Prepare category data for template
    $categoryData = null;
    if ($category) {
        $categoryData = [
            'id' => $category->id->value,
        ];
    }

    // Assign to template
    $GLOBALS['xoopsTpl']->assign('category', $categoryData);
    $GLOBALS['xoopsTpl']->assign('name', $name);
    $GLOBALS['xoopsTpl']->assign('description', $description);
    $GLOBALS['xoopsTpl']->assign('weight', $weight);
    $GLOBALS['xoopsTpl']->assign('xoops_security_token', $GLOBALS['xoopsSecurity']->getTokenHTML());

    // Display template
    $GLOBALS['xoopsTpl']->display('db:vision2026_admin_categories_form.tpl');
}

// ============================================================================
// Save Category
// ============================================================================
elseif ($op === 'save' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!$GLOBALS['xoopsSecurity']->check()) {
        redirect_header('categories.php', 3, 'Security token validation failed.');
        exit;
    }

    try {
        $name = $_POST['name'] ?? '';
        $description = $_POST['description'] ?? '';
        $weight = isset($_POST['weight']) ? (int)$_POST['weight'] : 0;
        $editId = $_POST['id'] ?? null;

        if ($editId) {
            // Update existing category
            $category = $categoryRepository->find(CategoryId::fromInt((int)$editId));
            if (!$category) {
                redirect_header('categories.php', 3, 'Category not found.');
                exit;
            }

            // Create new category with updated values (immutable pattern)
            $updatedCategory = Category::reconstitute(
                $category->id,
                CategoryName::fromString($name),
                CategorySlug::fromCategoryName(CategoryName::fromString($name)),
                $description,
                $weight,
                null
            );

            $categoryRepository->save($updatedCategory);
            redirect_header('categories.php', 2, _AM_VISION2026_SAVED);
        } else {
            // Create new category
            $categoryName = CategoryName::fromString($name);
            $category = Category::create(
                CategoryId::fromInt(0), // Auto-increment will assign ID
                $categoryName,
                $description,
                $weight
            );

            $categoryRepository->save($category);
            redirect_header('categories.php', 2, _AM_VISION2026_SAVED);
        }
    } catch (\Throwable $e) {
        redirect_header('categories.php', 3, _AM_VISION2026_ERROR_SAVE . ' ' . $e->getMessage());
    }
    exit;
}

// ============================================================================
// Delete Category
// ============================================================================
elseif ($op === 'delete' && $categoryId) {
    try {
        $category = $categoryRepository->find(CategoryId::fromInt((int)$categoryId));
        if ($category) {
            $categoryRepository->remove($category);
            redirect_header('categories.php', 2, _AM_VISION2026_DELETED);
        } else {
            redirect_header('categories.php', 3, 'Category not found.');
        }
    } catch (\Throwable $e) {
        redirect_header('categories.php', 3, _AM_VISION2026_ERROR_DELETE);
    }
    exit;
}

require_once __DIR__ . '/admin_footer.php';
