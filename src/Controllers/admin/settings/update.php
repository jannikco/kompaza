<?php

use App\Models\Tenant;

if (!isPost()) {
    redirect('/admin/indstillinger');
}

if (!verifyCsrfToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
    flashMessage('error', 'Invalid request. Please try again.');
    redirect('/admin/indstillinger');
}

$tenantId = currentTenantId();
$tenant = currentTenant();

// Validate email_service
$emailService = $_POST['email_service'] ?? 'kompaza';
if (!in_array($emailService, ['kompaza', 'brevo', 'mailgun', 'smtp'], true)) {
    $emailService = 'kompaza';
}

// Validate homepage_template
$homepageTemplate = $_POST['homepage_template'] ?? 'starter';
if (!in_array($homepageTemplate, ['starter', 'bold', 'elegant'], true)) {
    $homepageTemplate = 'starter';
}

// Map form field names to tenant columns (form uses contact_* aliases)
$data = [
    'homepage_template' => $homepageTemplate,
    'hero_subtitle' => sanitize($_POST['hero_subtitle'] ?? ''),
    'company_name' => sanitize($_POST['company_name'] ?? ''),
    'tagline' => sanitize($_POST['tagline'] ?? ''),
    'email' => sanitize($_POST['contact_email'] ?? $_POST['email'] ?? ''),
    'phone' => sanitize($_POST['contact_phone'] ?? $_POST['phone'] ?? ''),
    'address' => sanitize($_POST['contact_address'] ?? $_POST['address'] ?? ''),
    'cvr_number' => sanitize($_POST['cvr_number'] ?? ''),
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
    'smtp_encryption' => in_array($_POST['smtp_encryption'] ?? 'tls', ['tls', 'ssl', 'none'], true)
        ? $_POST['smtp_encryption']
        : 'tls',
    'stripe_publishable_key' => sanitize($_POST['stripe_publishable_key'] ?? ''),
    'stripe_secret_key' => sanitize($_POST['stripe_secret_key'] ?? ''),
    'google_analytics_id' => sanitize($_POST['google_analytics_id'] ?? ''),
    'custom_css' => $_POST['custom_css'] ?? '',
    'custom_footer_html' => $_POST['custom_footer_html'] ?? '',
];

// Handle logo upload → store as logo_url (schema + shop layout field)
if (!empty($_FILES['logo']['name']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
    $ext = strtolower(pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif', 'svg'], true)) {
        flashMessage('error', 'Only images (jpg, png, webp, gif, svg) are allowed for the logo.');
        redirect('/admin/indstillinger');
    }
    $oldLogo = $tenant['logo_url'] ?? $tenant['logo_path'] ?? null;
    if (!empty($oldLogo)) {
        deleteUploadedFile($oldLogo);
    }
    $data['logo_url'] = uploadPublicFile($_FILES['logo']['tmp_name'], 'branding', 'logo', $ext);
}

// Handle favicon upload if provided
if (!empty($_FILES['favicon']['name']) && $_FILES['favicon']['error'] === UPLOAD_ERR_OK) {
    $ext = strtolower(pathinfo($_FILES['favicon']['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif', 'ico', 'svg'], true)) {
        flashMessage('error', 'Only images (jpg, png, webp, gif, ico, svg) are allowed for the favicon.');
        redirect('/admin/indstillinger');
    }
    if (!empty($tenant['favicon_url'])) {
        deleteUploadedFile($tenant['favicon_url']);
    }
    $data['favicon_url'] = uploadPublicFile($_FILES['favicon']['tmp_name'], 'branding', 'favicon', $ext);
}

// Handle hero image upload
if (!empty($_FILES['hero_image']['name']) && $_FILES['hero_image']['error'] === UPLOAD_ERR_OK) {
    $ext = strtolower(pathinfo($_FILES['hero_image']['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif'], true)) {
        flashMessage('error', 'Only images (jpg, png, webp, gif) are allowed for the hero image.');
        redirect('/admin/indstillinger');
    }
    if (!empty($tenant['hero_image_path'])) {
        deleteUploadedFile($tenant['hero_image_path']);
    }
    $data['hero_image_path'] = uploadPublicFile($_FILES['hero_image']['tmp_name'], 'branding', 'hero', $ext);
}

Tenant::update($tenantId, $data);

logAudit('settings_updated', 'tenant', $tenantId);
flashMessage('success', 'Settings updated.');
redirect('/admin/indstillinger');
