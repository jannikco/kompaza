<?php

use App\Models\Tenant;

// Only POST allowed
if (!isPost()) {
    redirect('/login');
}

// CSRF check
if (!verifyCsrfToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
    flashMessage('error', 'Invalid request. Please try again.');
    redirect('/login');
}

// Rate limiting
$ip = getClientIp();
if (!checkRateLimit($ip, 'marketing_login', 10, 3600)) {
    setcookie('kz_login_error', 'Too many attempts. Please try again later.', time() + 60, '/', '', true, true);
    redirect('/login');
}

$email = trim($_POST['email'] ?? '');
$slug = trim($_POST['slug'] ?? '');
$slug = strtolower(preg_replace('/[^a-z0-9\-]/', '', $slug));

// Preserve old input
$old = [
    'email' => $email,
    'slug' => $slug,
];

// Validate
if (empty($email) || empty($slug)) {
    setcookie('kz_login_error', 'Please enter both your email and workspace slug.', time() + 60, '/', '', true, true);
    setcookie('kz_login_old', json_encode($old), time() + 60, '/', '', true, true);
    redirect('/login');
}

// Check if tenant exists
$tenant = Tenant::findBySlug($slug);
if (!$tenant) {
    setcookie('kz_login_error', 'Workspace "' . htmlspecialchars($slug) . '" was not found. Please check the subdomain and try again.', time() + 60, '/', '', true, true);
    setcookie('kz_login_old', json_encode($old), time() + 60, '/', '', true, true);
    redirect('/login');
}

if ($tenant['status'] === 'suspended') {
    setcookie('kz_login_error', 'This workspace has been suspended. Please contact support.', time() + 60, '/', '', true, true);
    setcookie('kz_login_old', json_encode($old), time() + 60, '/', '', true, true);
    redirect('/login');
}

if ($tenant['status'] === 'cancelled') {
    setcookie('kz_login_error', 'This workspace has been cancelled.', time() + 60, '/', '', true, true);
    setcookie('kz_login_old', json_encode($old), time() + 60, '/', '', true, true);
    redirect('/login');
}

// Redirect to the tenant's login page
$scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$loginUrl = $scheme . '://' . $slug . '.' . PLATFORM_DOMAIN . '/login';

// Pass the email as a query parameter so it can be pre-filled
$loginUrl .= '?email=' . urlencode($email);

redirect($loginUrl);
