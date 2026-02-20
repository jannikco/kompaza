<?php

use App\Models\Campaign;

$tenantId = currentTenantId();
$campaigns = Campaign::allByTenant($tenantId);

// Load LinkedIn account for display
$db = \App\Database\Database::getConnection();
$stmt = $db->prepare("SELECT id, linkedin_name FROM linkedin_accounts WHERE tenant_id = ? LIMIT 1");
$stmt->execute([$tenantId]);
$linkedinAccount = $stmt->fetch();

view('admin/leadshark/campaigns/index', [
    'tenant' => currentTenant(),
    'campaigns' => $campaigns,
    'linkedinAccount' => $linkedinAccount,
]);
