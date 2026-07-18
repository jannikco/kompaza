<?php

use App\Models\Tenant;

if (!isPost()) {
    redirect('/admin');
}

if (!verifyCsrfToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
    flashMessage('error', 'Invalid request.');
    redirect('/admin');
}

$tenantId = currentTenantId();
$tenant = currentTenant();

$onboarding = [];
if (!empty($tenant['onboarding_json'])) {
    $decoded = is_string($tenant['onboarding_json'])
        ? json_decode($tenant['onboarding_json'], true)
        : $tenant['onboarding_json'];
    if (is_array($decoded)) {
        $onboarding = $decoded;
    }
}

$onboarding['version'] = $onboarding['version'] ?? 1;
$onboarding['dismissed'] = true;
$onboarding['dismissed_at'] = date('c');

try {
    $db = \App\Database\Database::getConnection();
    $col = $db->query("SHOW COLUMNS FROM tenants LIKE 'onboarding_json'")->fetch();
    if ($col) {
        Tenant::update($tenantId, [
            'onboarding_json' => json_encode($onboarding),
        ]);
    }
} catch (\Exception $e) {
    // Column may not exist yet
}

flashMessage('success', 'Setup checklist dismissed. You can always use the sidebar to explore features.');
redirect('/admin');
