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

CustomPage::delete($id, $tenantId);

logAudit('custom_page_deleted', 'custom_page', $id);
flashMessage('success', 'Custom page deleted.');
redirect('/admin/custom-pages');
