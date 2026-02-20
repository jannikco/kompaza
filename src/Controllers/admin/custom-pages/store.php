<?php

use App\Models\CustomPage;

if (!isPost()) redirect('/admin/custom-pages');

if (!verifyCsrfToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
    flashMessage('error', 'Invalid CSRF token. Try again.');
    redirect('/admin/custom-pages/create');
}

$tenantId = currentTenantId();

$title = sanitize($_POST['title'] ?? '');
$slug = sanitize($_POST['slug'] ?? '') ?: slugify($title);
$status = sanitize($_POST['status'] ?? 'draft');
$layout = sanitize($_POST['layout'] ?? 'shop');
$isHomepage = !empty($_POST['is_homepage']);

if (!$title) {
    flashMessage('error', 'Title is required.');
    redirect('/admin/custom-pages/create');
}

// If setting as homepage, clear any existing homepage
if ($isHomepage) {
    CustomPage::clearHomepage($tenantId);
}

$id = CustomPage::create([
    'tenant_id' => $tenantId,
    'slug' => $slug,
    'title' => $title,
    'content' => $_POST['content'] ?? null,
    'layout' => $layout,
    'meta_description' => sanitize($_POST['meta_description'] ?? ''),
    'status' => $status,
    'is_homepage' => $isHomepage ? 1 : 0,
    'sort_order' => (int)($_POST['sort_order'] ?? 0),
]);

logAudit('custom_page_created', 'custom_page', $id);
flashMessage('success', 'Custom page created.');
redirect('/admin/custom-pages');
