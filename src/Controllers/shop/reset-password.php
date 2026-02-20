<?php

use App\Models\PasswordReset;

$tenant = currentTenant();
$token = $_GET['token'] ?? '';

if (empty($token)) {
    flashMessage('error', 'Invalid reset link.');
    redirect('/login');
}

$reset = PasswordReset::findByToken($token);
if (!$reset) {
    flashMessage('error', 'This reset link has expired or is invalid. Please request a new one.');
    redirect('/forgot-password');
}

view('shop/reset-password', [
    'tenant' => $tenant,
    'token' => $token,
]);
