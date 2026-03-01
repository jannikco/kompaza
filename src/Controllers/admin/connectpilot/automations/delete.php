<?php

use App\Models\PostAutomation;

if (!isPost()) redirect('/admin/connectpilot/automations');

if (!verifyCsrfToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
    flashMessage('error', 'Invalid CSRF token. Please try again.');
    redirect('/admin/connectpilot/automations');
}

$id = $_POST['id'] ?? null;
$tenantId = currentTenantId();

if (!$id) redirect('/admin/connectpilot/automations');

$automation = PostAutomation::find($id, $tenantId);
if (!$automation) {
    flashMessage('error', 'Automation not found.');
    redirect('/admin/connectpilot/automations');
}

PostAutomation::delete($id, $tenantId);

logAudit('post_automation_deleted', 'post_automation', $id);
flashMessage('success', 'Post automation deleted successfully.');
redirect('/admin/connectpilot/automations');
