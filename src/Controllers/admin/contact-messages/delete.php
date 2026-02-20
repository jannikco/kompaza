<?php

use App\Models\ContactMessage;

$tenantId = currentTenantId();

if (!verifyCsrfToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
    flashMessage('error', 'Invalid request.');
    redirect('/admin/contact-messages');
}

$messageId = (int)($_POST['id'] ?? 0);

ContactMessage::delete($messageId, $tenantId);

flashMessage('success', 'Message deleted.');
redirect('/admin/contact-messages');
