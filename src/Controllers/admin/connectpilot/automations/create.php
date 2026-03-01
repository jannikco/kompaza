<?php

$tenantId = currentTenantId();

// Load LinkedIn accounts for dropdown
$db = \App\Database\Database::getConnection();
$stmt = $db->prepare("SELECT id, linkedin_name, linkedin_email, status FROM linkedin_accounts WHERE tenant_id = ?");
$stmt->execute([$tenantId]);
$linkedinAccounts = $stmt->fetchAll();

// Load lead magnets for dropdown
$stmt = $db->prepare("SELECT id, title, slug FROM lead_magnets WHERE tenant_id = ? AND status = 'published' ORDER BY title ASC");
$stmt->execute([$tenantId]);
$leadMagnets = $stmt->fetchAll();

view('admin/connectpilot/automations/create', [
    'tenant' => currentTenant(),
    'linkedinAccounts' => $linkedinAccounts,
    'leadMagnets' => $leadMagnets,
]);
