<?php

use App\Models\PromptCategory;

$tenantId = currentTenantId();

// POST: create, update, or delete a category
if (isPost()) {
    if (!verifyCsrfToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
        flashMessage('error', 'Invalid CSRF token.');
        redirect('/admin/prompts/categories');
    }

    $action = $_POST['action'] ?? '';

    if ($action === 'create') {
        $name = sanitize($_POST['name'] ?? '');
        $slug = slugify($name);
        $description = sanitize($_POST['description'] ?? '');
        $icon = sanitize($_POST['icon'] ?? '');
        $sortOrder = (int)($_POST['sort_order'] ?? 0);

        if (!$name) {
            flashMessage('error', 'Category name is required.');
            redirect('/admin/prompts/categories');
        }

        $id = PromptCategory::create([
            'tenant_id' => $tenantId,
            'name' => $name,
            'slug' => $slug,
            'description' => $description ?: null,
            'icon' => $icon ?: null,
            'sort_order' => $sortOrder,
        ]);

        logAudit('prompt_category_created', 'prompt_category', $id);
        flashMessage('success', 'Category created.');
        redirect('/admin/prompts/categories');
    }

    if ($action === 'update') {
        $id = $_POST['id'] ?? null;
        if (!$id) redirect('/admin/prompts/categories');

        $category = PromptCategory::find($id, $tenantId);
        if (!$category) {
            flashMessage('error', 'Category not found.');
            redirect('/admin/prompts/categories');
        }

        $name = sanitize($_POST['name'] ?? '');
        $slug = slugify($name);
        $description = sanitize($_POST['description'] ?? '');
        $icon = sanitize($_POST['icon'] ?? '');
        $sortOrder = (int)($_POST['sort_order'] ?? 0);

        if (!$name) {
            flashMessage('error', 'Category name is required.');
            redirect('/admin/prompts/categories');
        }

        PromptCategory::update($id, [
            'name' => $name,
            'slug' => $slug,
            'description' => $description ?: null,
            'icon' => $icon ?: null,
            'sort_order' => $sortOrder,
        ]);

        logAudit('prompt_category_updated', 'prompt_category', $id);
        flashMessage('success', 'Category updated.');
        redirect('/admin/prompts/categories');
    }

    if ($action === 'delete') {
        $id = $_POST['id'] ?? null;
        if (!$id) redirect('/admin/prompts/categories');

        $category = PromptCategory::find($id, $tenantId);
        if (!$category) {
            flashMessage('error', 'Category not found.');
            redirect('/admin/prompts/categories');
        }

        PromptCategory::delete($id, $tenantId);

        logAudit('prompt_category_deleted', 'prompt_category', $id);
        flashMessage('success', 'Category deleted.');
        redirect('/admin/prompts/categories');
    }

    redirect('/admin/prompts/categories');
}

// GET: list all categories
$categories = PromptCategory::allByTenant($tenantId);

view('admin/prompts/categories', [
    'tenant' => currentTenant(),
    'categories' => $categories,
]);
