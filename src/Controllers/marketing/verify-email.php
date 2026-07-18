<?php

use App\Auth\Auth;
use App\Database\Database;
use App\Models\User;
use App\Models\Tenant;

$token = $_GET['token'] ?? '';

if (empty($token)) {
    flashMessage('error', 'Invalid verification link.');
    redirect('/login');
}

$db = Database::getConnection();

// Look up the token
$stmt = $db->prepare("
    SELECT * FROM email_verification_tokens
    WHERE token = ? AND expires_at > NOW()
    LIMIT 1
");
$stmt->execute([$token]);
$verification = $stmt->fetch();

if (!$verification) {
    flashMessage('error', 'This verification link is invalid or has expired. Please register again.');
    redirect('/register');
}

// Find the user
$user = User::find($verification['user_id']);
if (!$user) {
    flashMessage('error', 'Account not found. Please register again.');
    redirect('/register');
}

// Mark email as verified
User::update($user['id'], [
    'email_verified_at' => date('Y-m-d H:i:s'),
]);

// Delete the used token
$stmt = $db->prepare("DELETE FROM email_verification_tokens WHERE id = ?");
$stmt->execute([$verification['id']]);

// Log the user in
$user = User::find($user['id']); // Re-fetch with updated data
Auth::login($user);

// Audit
logAudit('email_verified', 'user', $user['id']);

// Redirect to tenant admin panel with welcome onboarding
$tenant = Tenant::find($user['tenant_id']);
if ($tenant) {
    // Send welcome email (best-effort)
    try {
        $loginUrl = 'https://' . $tenant['slug'] . '.' . PLATFORM_DOMAIN . '/admin?welcome=1';
        $userName = $user['name'] ?? '';
        $companyName = $tenant['company_name'] ?? $tenant['name'] ?? '';
        $subdomain = $tenant['slug'] . '.' . PLATFORM_DOMAIN;
        ob_start();
        include VIEWS_PATH . '/emails/welcome-tenant.php';
        $html = ob_get_clean();
        $brevo = new \App\Services\BrevoService();
        if (method_exists($brevo, 'sendTransactionalEmail')) {
            $brevo->sendTransactionalEmail($user['email'], 'Welcome to Kompaza — let\'s get you set up', $html);
        } elseif (method_exists($brevo, 'send')) {
            $brevo->send($user['email'], 'Welcome to Kompaza — let\'s get you set up', $html);
        }
    } catch (\Exception $e) {
        error_log('Welcome email failed: ' . $e->getMessage());
    }

    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $redirectUrl = $scheme . '://' . $tenant['slug'] . '.' . PLATFORM_DOMAIN . '/admin?welcome=1';
    redirect($redirectUrl);
} else {
    flashMessage('success', 'Email verified successfully! You can now log in.');
    redirect('/login');
}
