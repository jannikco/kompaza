<?php

use App\Models\PasswordReset;
use App\Database\Database;
use App\Services\EmailServiceFactory;

$tenant = currentTenant();

if (!verifyCsrfToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
    flashMessage('error', 'Invalid request. Please try again.');
    redirect('/forgot-password');
}

if (!checkRateLimit(getClientIp(), 'password_reset', 5, 3600)) {
    flashMessage('error', 'Too many requests. Please try again later.');
    redirect('/forgot-password');
}

$email = trim($_POST['email'] ?? '');

if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    flashMessage('error', 'Please enter a valid email address.');
    redirect('/forgot-password');
}

// Always show success to prevent email enumeration
$successMessage = 'If an account exists with that email, you will receive a password reset link.';

// Check if user exists for this tenant
$db = Database::getConnection();
$stmt = $db->prepare("SELECT id, email, name FROM users WHERE email = ? AND tenant_id = ?");
$stmt->execute([$email, $tenant['id']]);
$user = $stmt->fetch();

if ($user) {
    $token = PasswordReset::create($email);

    $resetUrl = url('reset-password?token=' . $token);
    $companyName = $tenant['company_name'] ?? $tenant['name'] ?? 'Our Platform';

    $htmlContent = '
    <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px;">
        <h2 style="color: #1f2937;">Reset Your Password</h2>
        <p style="color: #4b5563;">Hi ' . htmlspecialchars($user['name']) . ',</p>
        <p style="color: #4b5563;">You requested a password reset. Click the button below to set a new password:</p>
        <div style="text-align: center; margin: 30px 0;">
            <a href="' . $resetUrl . '" style="display: inline-block; background-color: #3b82f6; color: #ffffff; padding: 12px 32px; text-decoration: none; border-radius: 8px; font-weight: 600;">Reset Password</a>
        </div>
        <p style="color: #6b7280; font-size: 14px;">This link will expire in 1 hour.</p>
        <p style="color: #6b7280; font-size: 14px;">If you did not request this, you can safely ignore this email.</p>
        <hr style="border: none; border-top: 1px solid #e5e7eb; margin: 20px 0;">
        <p style="color: #9ca3af; font-size: 12px;">' . htmlspecialchars($companyName) . '</p>
    </div>';

    try {
        $emailService = EmailServiceFactory::create($tenant);
        $emailService->sendTransactionalEmail(
            ['email' => $user['email'], 'name' => $user['name']],
            'Reset Your Password',
            $htmlContent
        );
    } catch (\Exception $e) {
        error_log("Password reset email failed: " . $e->getMessage());
    }
}

flashMessage('success', $successMessage);
redirect('/forgot-password');
