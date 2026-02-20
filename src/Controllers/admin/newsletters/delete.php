<?php

use App\Models\Newsletter;

if (!isPost()) redirect('/admin/newsletters');

if (!verifyCsrfToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
    flashMessage('error', 'Invalid CSRF token. Please try again.');
    redirect('/admin/newsletters');
}

$id = $_POST['id'] ?? null;
$tenantId = currentTenantId();

if (!$id) redirect('/admin/newsletters');

$newsletter = Newsletter::find($id, $tenantId);
if (!$newsletter) {
    flashMessage('error', 'Newsletter not found.');
    redirect('/admin/newsletters');
}

Newsletter::delete($id, $tenantId);

logAudit('newsletter_deleted', 'newsletter', $id);
flashMessage('success', 'Newsletter deleted successfully.');
redirect('/admin/newsletters');
