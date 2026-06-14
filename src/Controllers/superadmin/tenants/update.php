<?php

use App\Models\Tenant;

if (!isPost()) {
    redirect('/tenants');
}

if (!verifyCsrfToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
    flashMessage('error', 'Invalid request. Please try again.');
    redirect('/tenants');
}

$id = (int)($_POST['id'] ?? 0);
if (!$id) {
    flashMessage('error', 'Tenant not found.');
    redirect('/tenants');
}

$tenant = Tenant::find($id);
if (!$tenant) {
    flashMessage('error', 'Tenant not found.');
    redirect('/tenants');
}

// ---- Identity ----
$name  = sanitize($_POST['name'] ?? '');
$slug  = slugify($_POST['slug'] ?? '');
$email = sanitize($_POST['email'] ?? '');

if (empty($name)) {
    flashMessage('error', 'Tenant name is required.');
    redirect('/tenants/edit?id=' . $id);
}
if (empty($slug)) {
    flashMessage('error', 'Slug is required.');
    redirect('/tenants/edit?id=' . $id);
}
if (Tenant::slugExists($slug, $id)) {
    flashMessage('error', 'This slug is already taken. Please choose another.');
    redirect('/tenants/edit?id=' . $id);
}

$status = sanitize($_POST['status'] ?? 'trial');
$validStatuses = ['trial', 'active', 'suspended', 'cancelled'];
if (!in_array($status, $validStatuses, true)) {
    $status = 'trial';
}

$planId = !empty($_POST['plan_id']) ? (int)$_POST['plan_id'] : null;

$data = [
    'name'   => $name,
    'slug'   => $slug,
    'email'  => $email ?: null,
    'status' => $status,
    'plan_id' => $planId,
];

$trialEndsAt = sanitize($_POST['trial_ends_at'] ?? '');
if ($trialEndsAt !== '') {
    $data['trial_ends_at'] = $trialEndsAt . ' 23:59:59';
} else {
    $data['trial_ends_at'] = null;
}

// ---- Features (checkbox => 1/0) ----
$featureColumns = [
    'feature_blog', 'feature_ebooks', 'feature_lead_magnets', 'feature_orders',
    'feature_connectpilot', 'feature_courses', 'feature_newsletters', 'feature_consultations',
    'feature_mastermind', 'feature_custom_pages', 'feature_memberships', 'feature_prompts',
    'feature_community',
];
foreach ($featureColumns as $col) {
    $data[$col] = isset($_POST[$col]) ? 1 : 0;
}

// ---- Branding ----
$primaryColor   = sanitize($_POST['primary_color'] ?? '');
$secondaryColor = sanitize($_POST['secondary_color'] ?? '');
$data['primary_color']   = preg_match('/^#[0-9a-fA-F]{6}$/', $primaryColor) ? $primaryColor : '#3b82f6';
$data['secondary_color'] = preg_match('/^#[0-9a-fA-F]{6}$/', $secondaryColor) ? $secondaryColor : '#6366f1';
$data['logo_url']           = sanitize($_POST['logo_url'] ?? '') ?: null;
$data['custom_domain']      = sanitize($_POST['custom_domain'] ?? '') ?: null;
$data['homepage_template']  = sanitize($_POST['homepage_template'] ?? 'starter') ?: 'starter';
// Raw HTML/CSS fields - stored verbatim, escaped on output. sanitize() would corrupt them.
$data['custom_css']         = ($_POST['custom_css'] ?? '') !== '' ? trim($_POST['custom_css']) : null;
$data['custom_footer_html'] = ($_POST['custom_footer_html'] ?? '') !== '' ? trim($_POST['custom_footer_html']) : null;

// ---- Integrations ----
$emailService = sanitize($_POST['email_service'] ?? 'kompaza');
$validEmailServices = ['kompaza', 'brevo', 'mailgun', 'smtp'];
if (!in_array($emailService, $validEmailServices, true)) {
    $emailService = 'kompaza';
}
$data['email_service']    = $emailService;
$data['brevo_api_key']    = sanitize($_POST['brevo_api_key'] ?? '') ?: null;
$data['mailgun_api_key']  = sanitize($_POST['mailgun_api_key'] ?? '') ?: null;
$data['mailgun_domain']   = sanitize($_POST['mailgun_domain'] ?? '') ?: null;
$data['smtp_host']        = sanitize($_POST['smtp_host'] ?? '') ?: null;
$smtpPort = (int)($_POST['smtp_port'] ?? 0);
$data['smtp_port']        = ($smtpPort > 0 && $smtpPort <= 65535) ? $smtpPort : 587;
$data['smtp_username']    = sanitize($_POST['smtp_username'] ?? '') ?: null;
$data['smtp_password']    = sanitize($_POST['smtp_password'] ?? '') ?: null;
$smtpEncryption = sanitize($_POST['smtp_encryption'] ?? 'tls');
$data['smtp_encryption']  = in_array($smtpEncryption, ['tls', 'ssl', 'none'], true) ? $smtpEncryption : 'tls';

$data['stripe_publishable_key'] = sanitize($_POST['stripe_publishable_key'] ?? '') ?: null;
$data['stripe_secret_key']      = sanitize($_POST['stripe_secret_key'] ?? '') ?: null;
$data['stripe_webhook_secret']  = sanitize($_POST['stripe_webhook_secret'] ?? '') ?: null;
$data['google_analytics_id']    = sanitize($_POST['google_analytics_id'] ?? '') ?: null;

// ---- Pricing ----
$currency = strtoupper(sanitize($_POST['currency'] ?? 'DKK'));
$data['currency'] = preg_match('/^[A-Z]{3}$/', $currency) ? $currency : 'DKK';
$taxRate = (float)($_POST['tax_rate'] ?? 0);
if ($taxRate < 0) { $taxRate = 0; }
if ($taxRate > 100) { $taxRate = 100; }
$data['tax_rate'] = $taxRate;

$result = Tenant::update($id, $data);

if ($result) {
    logAudit('tenant_updated', 'tenant', $id, ['name' => $name, 'slug' => $slug, 'status' => $status]);
    flashMessage('success', 'Tenant "' . $name . '" updated successfully.');
} else {
    flashMessage('error', 'Failed to update tenant. Please try again.');
}

redirect('/tenants/show?id=' . $id);
