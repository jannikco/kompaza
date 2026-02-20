<?php

use App\Models\ContactMessage;
use App\Services\EmailServiceFactory;

$tenantId = currentTenantId();

if (!verifyCsrfToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
    flashMessage('error', 'Invalid request.');
    redirect('/admin/contact-messages');
}

$messageId = (int)($_POST['id'] ?? 0);
$replyText = trim($_POST['reply'] ?? '');

if (empty($replyText)) {
    flashMessage('error', 'Reply text is required.');
    redirect('/admin/contact-messages');
}

$message = ContactMessage::find($messageId, $tenantId);
if (!$message) {
    flashMessage('error', 'Message not found.');
    redirect('/admin/contact-messages');
}

ContactMessage::reply($messageId, $tenantId, sanitize($replyText));

// Send reply email
try {
    $tenant = currentTenant();
    $companyName = $tenant['company_name'] ?? $tenant['name'] ?? 'Our Team';
    $htmlContent = '<div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px;">
        <h2 style="color: #1f2937;">Re: ' . htmlspecialchars($message['subject'] ?: 'Your Message') . '</h2>
        <p style="color: #4b5563;">Hi ' . htmlspecialchars($message['name']) . ',</p>
        <div style="background: #f3f4f6; padding: 16px; border-radius: 8px; margin: 12px 0;">
            <p style="white-space: pre-wrap; color: #1f2937;">' . htmlspecialchars($replyText) . '</p>
        </div>
        <hr style="border: none; border-top: 1px solid #e5e7eb; margin: 20px 0;">
        <p style="color: #9ca3af; font-size: 12px;">Your original message:</p>
        <p style="color: #6b7280; font-size: 13px; font-style: italic;">' . htmlspecialchars($message['message']) . '</p>
        <hr style="border: none; border-top: 1px solid #e5e7eb; margin: 20px 0;">
        <p style="color: #9ca3af; font-size: 12px;">' . htmlspecialchars($companyName) . '</p>
    </div>';

    $emailService = EmailServiceFactory::create($tenant);
    $emailService->sendTransactionalEmail(
        ['email' => $message['email'], 'name' => $message['name']],
        'Re: ' . ($message['subject'] ?: 'Your Message'),
        $htmlContent
    );
} catch (\Exception $e) {
    error_log("Contact reply email failed: " . $e->getMessage());
}

flashMessage('success', 'Reply sent to ' . $message['email']);
redirect('/admin/contact-messages');
