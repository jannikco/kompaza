<?php

$tenantId = currentTenantId();

// Load LinkedIn account for dropdown
$db = \App\Database\Database::getConnection();
$stmt = $db->prepare("SELECT id, linkedin_name, linkedin_email, status FROM linkedin_accounts WHERE tenant_id = ?");
$stmt->execute([$tenantId]);
$linkedinAccounts = $stmt->fetchAll();

view('admin/connectpilot/campaigns/create', [
    'tenant' => currentTenant(),
    'linkedinAccounts' => $linkedinAccounts,
]);
