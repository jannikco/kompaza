<?php

use App\Models\PostAutomation;

$tenantId = currentTenantId();
$automations = PostAutomation::allByTenant($tenantId);

// Load LinkedIn account for display
$db = \App\Database\Database::getConnection();
$stmt = $db->prepare("SELECT id, linkedin_name FROM linkedin_accounts WHERE tenant_id = ? LIMIT 1");
$stmt->execute([$tenantId]);
$linkedinAccount = $stmt->fetch();

view('admin/connectpilot/automations/index', [
    'tenant' => currentTenant(),
    'automations' => $automations,
    'linkedinAccount' => $linkedinAccount,
]);
