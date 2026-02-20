<?php

use App\Models\ContactMessage;
use App\Services\EmailServiceFactory;

$tenant = currentTenant();
$tenantId = currentTenantId();

if (!verifyCsrfToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
    flashMessage('error', 'Invalid request. Please try again.');
    redirect('/contact');
}

if (!checkRateLimit(getClientIp(), 'contact_form', 5, 3600)) {
    flashMessage('error', 'Too many submissions. Please try again later.');
    redirect('/contact');
}

$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$subject = trim($_POST['subject'] ?? '');
$message = trim($_POST['message'] ?? '');

$errors = [];
if (empty($name)) $errors[] = 'Name is required.';
if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Valid email is required.';
if (empty($message)) $errors[] = 'Message is required.';

if (!empty($errors)) {
    view('shop/contact', [
        'tenant' => $tenant,
        'errors' => $errors,
        'old' => ['name' => $name, 'email' => $email, 'subject' => $subject, 'message' => $message],
    ]);
    return;
}

ContactMessage::create([
    'tenant_id' => $tenantId,
    'name' => sanitize($name),
    'email' => $email,
    'subject' => sanitize($subject),
    'message' => sanitize($message),
    'ip_address' => getClientIp(),
]);

// Notify admin via email
try {
    $adminEmail = $tenant['contact_email'] ?? $tenant['email'] ?? null;
    if ($adminEmail) {
        $companyName = $tenant['company_name'] ?? $tenant['name'] ?? 'Kompaza';
        $htmlContent = '<div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px;">
            <h2 style="color: #1f2937;">New Contact Message</h2>
            <p><strong>From:</strong> ' . htmlspecialchars($name) . ' (' . htmlspecialchars($email) . ')</p>
            <p><strong>Subject:</strong> ' . htmlspecialchars($subject ?: 'No subject') . '</p>
            <div style="background: #f3f4f6; padding: 16px; border-radius: 8px; margin-top: 12px;">
                <p style="white-space: pre-wrap;">' . htmlspecialchars($message) . '</p>
            </div>
            <p style="color: #9ca3af; font-size: 12px; margin-top: 20px;">View and reply in your admin panel: /admin/contact-messages</p>
        </div>';

        $emailService = EmailServiceFactory::create($tenant);
        $emailService->sendTransactionalEmail(
            $adminEmail,
            'New contact message from ' . $name,
            $htmlContent
        );
    }
} catch (\Exception $e) {
    error_log("Contact notification email failed: " . $e->getMessage());
}

flashMessage('success', 'Thank you! Your message has been sent. We\'ll get back to you soon.');
redirect('/contact');
