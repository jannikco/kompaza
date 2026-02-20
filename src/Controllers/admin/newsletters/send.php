<?php

use App\Models\Newsletter;
use App\Models\EmailSignup;

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

if ($newsletter['status'] === 'sent') {
    flashMessage('error', 'This newsletter has already been sent.');
    redirect('/admin/newsletters');
}

// Get all subscribers for this tenant
$subscribers = EmailSignup::allByTenant($tenantId, 100000, 0);

if (empty($subscribers)) {
    flashMessage('error', 'No subscribers found. Cannot send newsletter.');
    redirect('/admin/newsletters/compose?id=' . $id);
}

// Send emails
$emailService = \App\Services\EmailServiceFactory::create();
$successCount = 0;
$failCount = 0;

foreach ($subscribers as $subscriber) {
    try {
        $emailService->sendTransactionalEmail(
            $subscriber['email'],
            $newsletter['subject'],
            $newsletter['body_html']
        );
        $successCount++;
    } catch (\Exception $e) {
        $failCount++;
        if (defined('APP_DEBUG') && APP_DEBUG) {
            error_log("Newsletter send failed to {$subscriber['email']}: " . $e->getMessage());
        }
    }
}

// Update newsletter status
Newsletter::update($id, [
    'status' => 'sent',
    'recipient_count' => $successCount,
    'sent_at' => date('Y-m-d H:i:s'),
]);

logAudit('newsletter_sent', 'newsletter', $id, [
    'success' => $successCount,
    'failed' => $failCount,
]);

if ($failCount > 0) {
    flashMessage('success', "Newsletter sent to {$successCount} subscriber(s). {$failCount} failed.");
} else {
    flashMessage('success', "Newsletter sent successfully to {$successCount} subscriber(s).");
}

redirect('/admin/newsletters');
