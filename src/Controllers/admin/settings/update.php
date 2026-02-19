<?php

use App\Models\Tenant;

if (!isPost()) redirect('/admin/settings');

if (!verifyCsrfToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
    flashMessage('error', 'Ugyldig CSRF-token. Prøv igen.');
    redirect('/admin/settings');
}

$tenantId = currentTenantId();
$tenant = currentTenant();

$data = [
    'company_name' => sanitize($_POST['company_name'] ?? ''),
    'tagline' => sanitize($_POST['tagline'] ?? ''),
    'email' => sanitize($_POST['email'] ?? ''),
    'phone' => sanitize($_POST['phone'] ?? ''),
    'primary_color' => sanitize($_POST['primary_color'] ?? '#3b82f6'),
    'secondary_color' => sanitize($_POST['secondary_color'] ?? '#6366f1'),
    'brevo_api_key' => sanitize($_POST['brevo_api_key'] ?? ''),
    'brevo_list_id' => sanitize($_POST['brevo_list_id'] ?? ''),
    'stripe_publishable_key' => sanitize($_POST['stripe_publishable_key'] ?? ''),
    'stripe_secret_key' => sanitize($_POST['stripe_secret_key'] ?? ''),
    'google_analytics_id' => sanitize($_POST['google_analytics_id'] ?? ''),
    'custom_css' => $_POST['custom_css'] ?? '',
];

// Handle logo upload
if (!empty($_FILES['logo']['name']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
    $imgOriginal = $_FILES['logo']['name'];
    $ext = strtolower(pathinfo($imgOriginal, PATHINFO_EXTENSION));
    if (!in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif', 'svg'])) {
        flashMessage('error', 'Kun billeder (jpg, png, webp, gif, svg) er tilladt til logo.');
        redirect('/admin/settings');
    }
    // Delete old logo
    if (!empty($tenant['logo_path'])) {
        $oldLogo = PUBLIC_PATH . $tenant['logo_path'];
        if (file_exists($oldLogo)) unlink($oldLogo);
    }
    $logoFilename = generateUniqueId('logo_') . '.' . $ext;
    $uploadPath = tenantUploadPath('branding');
    move_uploaded_file($_FILES['logo']['tmp_name'], $uploadPath . '/' . $logoFilename);
    $data['logo_path'] = '/uploads/' . $tenantId . '/branding/' . $logoFilename;
}

Tenant::update($tenantId, $data);

logAudit('settings_updated', 'tenant', $tenantId);
flashMessage('success', 'Indstillinger opdateret.');
redirect('/admin/settings');
