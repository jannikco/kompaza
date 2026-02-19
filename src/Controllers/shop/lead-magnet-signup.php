<?php

use App\Models\LeadMagnet;
use App\Models\EmailSignup;
use App\Models\DownloadToken;
use App\Services\BrevoService;

$tenant = currentTenant();
$tenantId = currentTenantId();

// Verify CSRF
$csrfToken = $_POST[CSRF_TOKEN_NAME] ?? '';
if (!verifyCsrfToken($csrfToken)) {
    flashMessage('error', 'Ugyldig anmodning. Prøv venligst igen.');
    redirect($_SERVER['HTTP_REFERER'] ?? '/');
}

// Validate input
$email = trim($_POST['email'] ?? '');
$name = sanitize($_POST['name'] ?? '');
$leadMagnetSlug = sanitize($_POST['lead_magnet_slug'] ?? '');

if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    flashMessage('error', 'Indtast venligst en gyldig e-mailadresse.');
    redirect('/lp/' . $leadMagnetSlug);
}

if (empty($name)) {
    flashMessage('error', 'Indtast venligst dit navn.');
    redirect('/lp/' . $leadMagnetSlug);
}

// Rate limit: 5 per hour per IP
$ip = getClientIp();
if (!checkRateLimit($ip, 'lead_magnet_signup', 5, 3600)) {
    flashMessage('error', 'For mange tilmeldinger. Prøv igen senere.');
    redirect('/lp/' . $leadMagnetSlug);
}

// Find the lead magnet
$leadMagnet = LeadMagnet::findBySlug($leadMagnetSlug, $tenantId);
if (!$leadMagnet || $leadMagnet['status'] !== 'published') {
    http_response_code(404);
    view('errors/404');
    exit;
}

// Create email signup record
EmailSignup::create([
    'tenant_id' => $tenantId,
    'email' => $email,
    'name' => $name,
    'source_type' => 'lead_magnet',
    'source_id' => $leadMagnet['id'],
    'source_slug' => $leadMagnetSlug,
    'ip_address' => $ip,
    'user_agent' => getUserAgent(),
]);

// Increment lead magnet signup count
LeadMagnet::incrementSignups($leadMagnet['id']);

// Create download token
$token = DownloadToken::create([
    'tenant_id' => $tenantId,
    'tokenable_type' => 'lead_magnet',
    'tokenable_id' => $leadMagnet['id'],
    'email' => $email,
    'max_downloads' => 5,
    'expires_at' => date('Y-m-d H:i:s', strtotime('+72 hours')),
]);

$downloadUrl = url('lp/download/' . $token);

// Send email via Brevo if configured
try {
    $brevo = new BrevoService(null, $tenantId);
    if ($brevo->isConfigured()) {
        $brevo->sendLeadMagnetEmail($email, $name, $leadMagnet, $downloadUrl);

        // Sync contact to Brevo list if configured
        $listId = !empty($leadMagnet['brevo_list_id']) ? (int)$leadMagnet['brevo_list_id'] : null;
        $brevo->addContact($email, $name, $listId);
    }
} catch (Exception $e) {
    if (APP_DEBUG) {
        error_log('Lead magnet email failed: ' . $e->getMessage());
    }
}

logAudit('lead_magnet_signup', 'lead_magnet', $leadMagnet['id'], [
    'email' => $email,
    'name' => $name,
]);

redirect('/lp/succes/' . $leadMagnetSlug);
