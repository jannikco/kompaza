<?php

use App\Models\Tenant;
use App\Models\Admin;
use App\Database\Database;

// Verify CSRF
$csrfToken = $_POST[CSRF_TOKEN_NAME] ?? '';
if (!verifyCsrfToken($csrfToken)) {
    flashMessage('error', 'Ugyldig forespørgsel. Prøv igen.');
    redirect('/tenants');
}

$tenantId = (int) ($_POST['tenant_id'] ?? 0);
if (!$tenantId) {
    flashMessage('error', 'Ugyldig tenant.');
    redirect('/tenants');
}

// Find tenant
$tenant = Tenant::find($tenantId);
if (!$tenant) {
    flashMessage('error', 'Tenant ikke fundet.');
    redirect('/tenants');
}

// Find the tenant's admin user: prefer owner_admin_id, fallback to first admin
$userId = $tenant['owner_admin_id'];

if (!$userId) {
    // Fallback: find first admin (in a multi-tenant setup you'd scope this to tenant)
    $db = Database::getConnection();
    $stmt = $db->query("SELECT id FROM admins WHERE is_superadmin = 0 ORDER BY id ASC LIMIT 1");
    $row = $stmt->fetch();
    $userId = $row['id'] ?? null;
}

if (!$userId) {
    flashMessage('error', 'Ingen admin-bruger fundet for denne tenant.');
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
