<?php

use App\Database\Database;
use App\Models\Tenant;
use App\Models\User;
use App\Models\Plan;
use App\Services\BrevoService;

// Only POST allowed
if (!isPost()) {
    redirect('/register');
}

// CSRF check
if (!verifyCsrfToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
    flashMessage('error', 'Invalid request. Please try again.');
    redirect('/register');
}

// Honeypot check — bots fill this hidden field, humans don't
if (!empty($_POST['website'])) {
    // Silently reject — show success page to not tip off the bot
    redirect('/verify-pending?email=' . urlencode($_POST['email'] ?? ''));
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
        'trial_ends_at' => date('Y-m-d H:i:s', strtotime('+7 days')),
        'subscription_status' => 'trialing',
    ]);

    // Create tenant admin user (email_verified_at stays NULL until verified)
    $userId = User::create([
        'tenant_id' => $tenantId,
        'role' => 'tenant_admin',
        'name' => $name,
        'email' => $email,
        'password' => $password,
        'company' => $companyName,
        'status' => 'active',
    ]);

    // Apply plan feature flags from features_json so Growth/Enterprise unlock correctly
    $tenantFeatureUpdate = [
        'owner_user_id' => $userId,
    ];
    if ($plan && !empty($plan['features_json'])) {
        $features = is_string($plan['features_json'])
            ? json_decode($plan['features_json'], true)
            : $plan['features_json'];
        if (is_array($features)) {
            $featureMap = [
                'blog' => 'feature_blog',
                'ebooks' => 'feature_ebooks',
                'lead_magnets' => 'feature_lead_magnets',
                'orders' => 'feature_orders',
                'connectpilot' => 'feature_connectpilot',
                'courses' => 'feature_courses',
                'newsletters' => 'feature_newsletters',
                'consultations' => 'feature_consultations',
                'mastermind' => 'feature_mastermind',
                'custom_pages' => 'feature_custom_pages',
                'memberships' => 'feature_memberships',
                'prompts' => 'feature_prompts',
                'community' => 'feature_community',
            ];
            foreach ($featureMap as $jsonKey => $column) {
                if (array_key_exists($jsonKey, $features)) {
                    $tenantFeatureUpdate[$column] = !empty($features[$jsonKey]) ? 1 : 0;
                }
            }
        }
    }
    // Seed onboarding checklist state when column exists (migration 031)
    try {
        $dbCheck = Database::getConnection();
        $col = $dbCheck->query("SHOW COLUMNS FROM tenants LIKE 'onboarding_json'")->fetch();
        if ($col) {
            $tenantFeatureUpdate['onboarding_json'] = json_encode([
                'version' => 1,
                'dismissed' => false,
                'steps' => [
                    'branding' => false,
                    'homepage' => false,
                    'first_content' => false,
                    'payments' => false,
                    'preview_site' => false,
                ],
            ]);
        }
    } catch (\Exception $e) {
        // Column may not exist yet — checklist falls back to client defaults
    }
    Tenant::update($tenantId, $tenantFeatureUpdate);

    // Generate email verification token
    $token = bin2hex(random_bytes(32));
    $db = Database::getConnection();
    $stmt = $db->prepare("
        INSERT INTO email_verification_tokens (user_id, token, expires_at, created_at)
        VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 24 HOUR), NOW())
    ");
    $stmt->execute([$userId, $token]);

    Database::commit();

    // Send verification email via Brevo
    try {
        $brevo = new BrevoService();
        $brevo->sendVerificationEmail($email, $name, $token);
    } catch (\Exception $mailErr) {
        error_log('Verification email failed: ' . $mailErr->getMessage());
    }

    // Audit
    logAudit('tenant_created', 'tenant', $tenantId, [
        'company_name' => $companyName,
        'slug' => $slug,
        'plan' => $planSlug,
    ]);

    // Redirect to "check your email" page (NOT auto-logged in)
    redirect('/verify-pending?email=' . urlencode($email));

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
