<?php

use App\Models\Tenant;
use App\Models\User;
use App\Database\Database;

// Verify CSRF
$csrfToken = $_POST[CSRF_TOKEN_NAME] ?? '';
if (!verifyCsrfToken($csrfToken)) {
    flashMessage('error', 'Invalid request. Please try again.');
    redirect('/tenants');
}

$tenantId = (int) ($_POST['tenant_id'] ?? 0);
if (!$tenantId) {
    flashMessage('error', 'Invalid tenant.');
    redirect('/tenants');
}

// Find tenant
$tenant = Tenant::find($tenantId);
if (!$tenant) {
    flashMessage('error', 'Tenant not found.');
    redirect('/tenants');
}

// Find the tenant's admin user
$db = Database::getConnection();
$stmt = $db->prepare("SELECT id FROM users WHERE tenant_id = ? AND role = 'tenant_admin' ORDER BY id ASC LIMIT 1");
$stmt->execute([$tenantId]);
$row = $stmt->fetch();
$userId = $row['id'] ?? null;

if (!$userId) {
    flashMessage('error', 'No admin user found for this tenant.');
    redirect('/tenants');
}

// Generate HMAC-signed URL
$timestamp = time();
$sig = hash_hmac('sha256', $userId . '|' . $timestamp, APP_SECRET);

// Audit log
logAudit('tenant_impersonate', 'tenant', $tenantId, ['target_user_id' => $userId]);

// Redirect to tenant site
$url = 'https://' . $tenant['slug'] . '.' . PLATFORM_DOMAIN . '/auth/impersonate?uid=' . $userId . '&ts=' . $timestamp . '&sig=' . $sig;
redirect($url);
