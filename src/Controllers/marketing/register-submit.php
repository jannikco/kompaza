<?php

use App\Auth\Auth;
use App\Database\Database;
use App\Models\Tenant;
use App\Models\User;
use App\Models\Plan;

// Only POST allowed
if (!isPost()) {
    redirect('/register');
}

// CSRF check
if (!verifyCsrfToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
    flashMessage('error', 'Invalid request. Please try again.');
    redirect('/register');
}

// Rate limiting
$ip = getClientIp();
if (!checkRateLimit($ip, 'register', 5, 3600)) {
    flashMessage('error', 'Too many registration attempts. Please try again later.');
    redirect('/register');
}

// Collect and sanitize input
$companyName = trim($_POST['company_name'] ?? '');
$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$slug = trim($_POST['slug'] ?? '');
$planSlug = trim($_POST['plan'] ?? 'starter');

// Preserve old input for re-display
$old = [
    'company_name' => $companyName,
    'name' => $name,
    'email' => $email,
    'slug' => $slug,
];

// ---- Validation ----
$errors = [];

if (empty($companyName) || mb_strlen($companyName) < 2) {
    $errors[] = 'Company name is required and must be at least 2 characters.';
}
if (mb_strlen($companyName) > 255) {
    $errors[] = 'Company name must be 255 characters or less.';
}

if (empty($name) || mb_strlen($name) < 2) {
    $errors[] = 'Your name is required and must be at least 2 characters.';
}
if (mb_strlen($name) > 255) {
    $errors[] = 'Name must be 255 characters or less.';
}

if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'A valid email address is required.';
}

if (empty($password) || mb_strlen($password) < 8) {
    $errors[] = 'Password must be at least 8 characters.';
}
if (mb_strlen($password) > 255) {
    $errors[] = 'Password must be 255 characters or less.';
}

// Validate slug
$slug = strtolower($slug);
$slug = preg_replace('/[^a-z0-9\-]/', '', $slug);
$slug = preg_replace('/-+/', '-', $slug);
$slug = trim($slug, '-');

if (empty($slug) || mb_strlen($slug) < 3) {
    $errors[] = 'Subdomain is required and must be at least 3 characters (letters, numbers, hyphens only).';
}
if (mb_strlen($slug) > 50) {
    $errors[] = 'Subdomain must be 50 characters or less.';
}

// Check reserved slugs
$reservedSlugs = ['www', 'superadmin', 'admin', 'api', 'app', 'mail', 'ftp', 'smtp', 'pop', 'imap', 'ns1', 'ns2', 'cpanel', 'webmail', 'localhost', 'test', 'staging', 'dev', 'help', 'support', 'status', 'blog', 'docs'];
if (in_array($slug, $reservedSlugs)) {
    $errors[] = 'This subdomain is reserved. Please choose a different one.';
}

// Check slug uniqueness
if (!empty($slug) && Tenant::slugExists($slug)) {
    $errors[] = 'This subdomain is already taken. Please choose a different one.';
}

// Check if email is already used as tenant_admin globally
if (!empty($email)) {
    $existingUser = User::findByEmailGlobal($email);
    if ($existingUser && $existingUser['role'] === 'tenant_admin') {
        $errors[] = 'An account with this email address already exists.';
    }
}

// If validation errors, redirect back with errors
if (!empty($errors)) {
    setcookie('kz_register_errors', json_encode($errors), time() + 60, '/', '', true, true);
    setcookie('kz_register_old', json_encode($old), time() + 60, '/', '', true, true);
    redirect('/register' . (!empty($planSlug) ? '?plan=' . urlencode($planSlug) : ''));
}

// ---- Create tenant + user in a transaction ----
try {
    Database::beginTransaction();

    // Resolve plan
    $plan = Plan::findBySlug($planSlug);
    $planId = $plan ? $plan['id'] : null;

    // Create tenant
    $tenantId = Tenant::create([
        'uuid' => generateUuid(),
        'name' => $companyName,
        'slug' => $slug,
        'status' => 'trial',
        'company_name' => $companyName,
        'email' => $email,
        'plan_id' => $planId,
        'trial_ends_at' => date('Y-m-d H:i:s', strtotime('+14 days')),
    ]);

    // Create tenant admin user
    $userId = User::create([
        'tenant_id' => $tenantId,
        'role' => 'tenant_admin',
        'name' => $name,
        'email' => $email,
        'password' => $password,
        'company' => $companyName,
        'status' => 'active',
    ]);

    // Set tenant owner
    Tenant::update($tenantId, [
        'owner_user_id' => $userId,
    ]);

    Database::commit();

    // Log the user in
    $user = User::find($userId);
    Auth::login($user);

    // Audit
    logAudit('tenant_created', 'tenant', $tenantId, [
        'company_name' => $companyName,
        'slug' => $slug,
        'plan' => $planSlug,
    ]);

    // Redirect to the new tenant's admin panel
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $redirectUrl = $scheme . '://' . $slug . '.' . PLATFORM_DOMAIN . '/admin';
    redirect($redirectUrl);

} catch (\Exception $e) {
    Database::rollback();

    if (APP_DEBUG) {
        error_log('Registration failed: ' . $e->getMessage());
    }

    $errors[] = 'An unexpected error occurred during registration. Please try again.';
    setcookie('kz_register_errors', json_encode($errors), time() + 60, '/', '', true, true);
    setcookie('kz_register_old', json_encode($old), time() + 60, '/', '', true, true);
    redirect('/register' . (!empty($planSlug) ? '?plan=' . urlencode($planSlug) : ''));
}
