<?php

use App\Auth\Auth;
use App\Models\User;

if (!isPost()) {
    redirect('/login');
}

if (!verifyCsrfToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
    flashMessage('error', 'Invalid security token. Please try again.');
    redirect('/login');
}

$email = sanitize($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

if (empty($email) || empty($password)) {
    flashMessage('error', 'Email and password are required.');
    redirect('/login');
}

// Rate limit check
if (!checkRateLimit(getClientIp(), 'superadmin_login', 100, 900)) {
    flashMessage('error', 'Too many login attempts. Please try again later.');
    redirect('/login');
}

// Attempt login with null tenantId (superadmin users have tenant_id = NULL)
if (!Auth::attempt($email, $password, null)) {
    flashMessage('error', 'Invalid email or password.');
    redirect('/login');
}

// Verify the user has superadmin role
$user = Auth::user();
if (!$user || $user['role'] !== 'superadmin') {
    Auth::logout();
    flashMessage('error', 'Access denied. Superadmin privileges required.');
    redirect('/login');
}

redirect('/');
