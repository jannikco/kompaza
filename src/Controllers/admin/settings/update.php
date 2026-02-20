<?php

use App\Models\Tenant;

if (!isPost()) redirect('/admin/settings');

if (!verifyCsrfToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
    flashMessage('error', 'Ugyldig CSRF-token. Prøv igen.');
    redirect('/admin/settings');
}

$tenantId = currentTenantId();
$tenant = currentTenant();

// Validate email_service
$emailService = $_POST['email_service'] ?? 'kompaza';
if (!in_array($emailService, ['kompaza', 'brevo', 'mailgun', 'smtp'])) {
    $emailService = 'kompaza';
}

$data = [
    'company_name' => sanitize($_POST['company_name'] ?? ''),
    'tagline' => sanitize($_POST['tagline'] ?? ''),
    'email' => sanitize($_POST['email'] ?? ''),
    'phone' => sanitize($_POST['phone'] ?? ''),
    'primary_color' => sanitize($_POST['primary_color'] ?? '#3b82f6'),
    'secondary_color' => sanitize($_POST['secondary_color'] ?? '#6366f1'),
    'email_service' => $emailService,
    'brevo_api_key' => sanitize($_POST['brevo_api_key'] ?? ''),
    'brevo_list_id' => sanitize($_POST['brevo_list_id'] ?? ''),
    'mailgun_api_key' => sanitize($_POST['mailgun_api_key'] ?? ''),
    'mailgun_domain' => sanitize($_POST['mailgun_domain'] ?? ''),
    'smtp_host' => sanitize($_POST['smtp_host'] ?? ''),
    'smtp_port' => (int)($_POST['smtp_port'] ?? 587),
    'smtp_username' => sanitize($_POST['smtp_username'] ?? ''),
    'smtp_password' => $_POST['smtp_password'] ?? '',
    'smtp_encryption' => in_array($_POST['smtp_encryption'] ?? 'tls', ['tls', 'ssl', 'none']) ? $_POST['smtp_encryption'] : 'tls',
    'stripe_publishable_key' => sanitize($_POST['stripe_publishable_key'] ?? ''),
    'stripe_secret_key' => sanitize($_POST['stripe_secret_key'] ?? ''),
    'google_analytics_id' => sanitize($_POST['google_analytics_id'] ?? ''),
    'custom_css' => $_POST['custom_css'] ?? '',
];

// Handle logo upload
if (!empty($_FILES['logo']['name']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
    $ext = strtolower(pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif', 'svg'])) {
        flashMessage('error', 'Kun billeder (jpg, png, webp, gif, svg) er tilladt til logo.');
        redirect('/admin/settings');
    }
    // Delete old logo
    if (!empty($tenant['logo_path'])) {
        deleteUploadedFile($tenant['logo_path']);
    }
    $data['logo_path'] = uploadPublicFile($_FILES['logo']['tmp_name'], 'branding', 'logo', $ext);
}

Tenant::update($tenantId, $data);

logAudit('settings_updated', 'tenant', $tenantId);
flashMessage('success', 'Indstillinger opdateret.');
redirect('/admin/settings');
