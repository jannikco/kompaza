<?php

use App\Models\Newsletter;

if (!isPost()) redirect('/admin/newsletters');

if (!verifyCsrfToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
    flashMessage('error', 'Invalid CSRF token. Please try again.');
    redirect('/admin/newsletters');
}

$tenantId = currentTenantId();
$id = $_POST['id'] ?? null;

if (!$id) redirect('/admin/newsletters');

$newsletter = Newsletter::find($id, $tenantId);
if (!$newsletter) {
    flashMessage('error', 'Newsletter not found.');
    redirect('/admin/newsletters');
}

$user = currentUser();
$testEmail = $user['email'];

try {
    $emailService = \App\Services\EmailServiceFactory::create();
    $emailService->sendTransactionalEmail(
        $testEmail,
        '[TEST] ' . $newsletter['subject'],
        $newsletter['body_html']
    );
    flashMessage('success', 'Test email sent to ' . $testEmail);
} catch (\Exception $e) {
    flashMessage('error', 'Failed to send test email: ' . $e->getMessage());
}

redirect('/admin/newsletters/compose?id=' . $id);
