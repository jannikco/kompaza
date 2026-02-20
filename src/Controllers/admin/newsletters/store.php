<?php

use App\Models\Newsletter;

if (!isPost()) redirect('/admin/newsletters');

if (!verifyCsrfToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
    flashMessage('error', 'Invalid CSRF token. Please try again.');
    redirect('/admin/newsletters/compose');
}

$tenantId = currentTenantId();
$subject = sanitize($_POST['subject'] ?? '');
$bodyHtml = $_POST['body_html'] ?? '';
$id = $_POST['id'] ?? null;

if (empty($subject)) {
    flashMessage('error', 'Subject is required.');
    redirect('/admin/newsletters/compose' . ($id ? '?id=' . $id : ''));
}

if ($id) {
    // Update existing draft
    $newsletter = Newsletter::find($id, $tenantId);
    if (!$newsletter) {
        flashMessage('error', 'Newsletter not found.');
        redirect('/admin/newsletters');
    }
    if ($newsletter['status'] === 'sent') {
        flashMessage('error', 'Cannot edit a sent newsletter.');
        redirect('/admin/newsletters');
    }
    Newsletter::update($id, [
        'subject' => $subject,
        'body_html' => $bodyHtml,
    ]);
    flashMessage('success', 'Newsletter draft saved.');
    redirect('/admin/newsletters/compose?id=' . $id);
} else {
    // Create new draft
    $newId = Newsletter::create([
        'tenant_id' => $tenantId,
        'subject' => $subject,
        'body_html' => $bodyHtml,
        'status' => 'draft',
    ]);
    flashMessage('success', 'Newsletter draft created.');
    redirect('/admin/newsletters/compose?id=' . $newId);
}
