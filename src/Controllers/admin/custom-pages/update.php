<?php

use App\Models\CustomPage;

if (!isPost()) redirect('/admin/custom-pages');

if (!verifyCsrfToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
    flashMessage('error', 'Invalid CSRF token. Try again.');
    redirect('/admin/custom-pages');
}

$id = $_POST['id'] ?? null;
$tenantId = currentTenantId();

if (!$id) redirect('/admin/custom-pages');

$page = CustomPage::find($id, $tenantId);
if (!$page) {
    flashMessage('error', 'Page not found.');
    redirect('/admin/custom-pages');
}

$isHomepage = !empty($_POST['is_homepage']);

// If setting as homepage, clear any existing homepage
if ($isHomepage) {
    CustomPage::clearHomepage($tenantId);
}

$data = [
    'title' => sanitize($_POST['title'] ?? ''),
    'slug' => sanitize($_POST['slug'] ?? '') ?: slugify($_POST['title'] ?? ''),
    'content' => $_POST['content'] ?? null,
    'layout' => sanitize($_POST['layout'] ?? 'shop'),
    'meta_description' => sanitize($_POST['meta_description'] ?? ''),
    'status' => sanitize($_POST['status'] ?? 'draft'),
    'is_homepage' => $isHomepage ? 1 : 0,
    'sort_order' => (int)($_POST['sort_order'] ?? 0),
];

CustomPage::update($id, $data);

logAudit('custom_page_updated', 'custom_page', $id);
flashMessage('success', 'Custom page updated.');
redirect('/admin/custom-pages');
