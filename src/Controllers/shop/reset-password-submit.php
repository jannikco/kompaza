<?php

use App\Models\PasswordReset;
use App\Database\Database;

$tenant = currentTenant();

if (!verifyCsrfToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
    flashMessage('error', 'Invalid request. Please try again.');
    redirect('/login');
}

$token = $_POST['token'] ?? '';
$password = $_POST['password'] ?? '';
$passwordConfirm = $_POST['password_confirmation'] ?? '';

if (empty($token)) {
    flashMessage('error', 'Invalid reset link.');
    redirect('/login');
}

$reset = PasswordReset::findByToken($token);
if (!$reset) {
    flashMessage('error', 'This reset link has expired or is invalid.');
    redirect('/forgot-password');
}

if (strlen($password) < 8) {
    view('shop/reset-password', [
        'tenant' => $tenant,
        'token' => $token,
        'error' => 'Password must be at least 8 characters.',
    ]);
    return;
}

if ($password !== $passwordConfirm) {
    view('shop/reset-password', [
        'tenant' => $tenant,
        'token' => $token,
        'error' => 'Passwords do not match.',
    ]);
    return;
}

// Update password
$db = Database::getConnection();
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);
$stmt = $db->prepare("UPDATE users SET password = ? WHERE email = ? AND tenant_id = ?");
$stmt->execute([$hashedPassword, $reset['email'], $tenant['id']]);

// Delete token
PasswordReset::delete($token);

flashMessage('success', 'Your password has been reset. You can now log in.');
redirect('/login');
