<?php

use App\Models\EmailSignup;
use App\Services\BrevoService;

header('Content-Type: application/json');

$tenant = currentTenant();
$tenantId = currentTenantId();

// Parse JSON input or form data
$input = json_decode(file_get_contents('php://input'), true) ?? $_POST;

$email = trim($input['email'] ?? '');

if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(422);
    echo json_encode(['success' => false, 'message' => 'Indtast venligst en gyldig e-mailadresse.']);
    exit;
}

// Rate limit: 5 signups per hour per IP
$ip = getClientIp();
if (!checkRateLimit($ip, 'newsletter_signup', 5, 3600)) {
    http_response_code(429);
    echo json_encode(['success' => false, 'message' => 'For mange tilmeldinger. Prøv igen senere.']);
    exit;
}

$name = sanitize($input['name'] ?? '');

// Create email signup record
EmailSignup::create([
    'tenant_id' => $tenantId,
    'email' => $email,
    'name' => $name ?: null,
    'source_type' => 'newsletter',
    'source_id' => null,
    'source_slug' => null,
    'ip_address' => $ip,
    'user_agent' => getUserAgent(),
]);

// Sync to Brevo if configured
try {
    $brevo = new BrevoService(null, $tenantId);
    if ($brevo->isConfigured()) {
        $listId = tenantSetting('brevo_newsletter_list_id');
        $brevo->addContact($email, $name, $listId ? (int)$listId : null);
    }
} catch (Exception $e) {
    if (APP_DEBUG) {
        error_log('Newsletter Brevo sync failed: ' . $e->getMessage());
    }
}

logAudit('newsletter_signup', 'email_signup', null, ['email' => $email]);

echo json_encode(['success' => true, 'message' => 'Tak for din tilmelding!']);
