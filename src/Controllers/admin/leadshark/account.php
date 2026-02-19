<?php

$tenantId = currentTenantId();
$tenant = currentTenant();

// Load existing LinkedIn account
$db = \App\Database\Database::getConnection();
$stmt = $db->prepare("SELECT * FROM linkedin_accounts WHERE tenant_id = ? LIMIT 1");
$stmt->execute([$tenantId]);
$linkedinAccount = $stmt->fetch();

view('admin/leadshark/account', [
    'tenant' => $tenant,
    'linkedinAccount' => $linkedinAccount,
]);
