<?php

use App\Models\Campaign;

$id = $_GET['id'] ?? null;
$tenantId = currentTenantId();

if (!$id) {
    http_response_code(404);
    view('errors/404');
    exit;
}

$campaign = Campaign::find($id, $tenantId);

if (!$campaign) {
    http_response_code(404);
    view('errors/404');
    exit;
}

// Load LinkedIn accounts for dropdown
$db = \App\Database\Database::getConnection();
$stmt = $db->prepare("SELECT id, linkedin_name, linkedin_email, status FROM linkedin_accounts WHERE tenant_id = ?");
$stmt->execute([$tenantId]);
$linkedinAccounts = $stmt->fetchAll();

// Load campaign steps
$stmt = $db->prepare("SELECT * FROM connectpilot_campaign_steps WHERE campaign_id = ? ORDER BY step_order ASC");
$stmt->execute([$id]);
$steps = $stmt->fetchAll();

view('admin/connectpilot/campaigns/edit', [
    'tenant' => currentTenant(),
    'campaign' => $campaign,
    'linkedinAccounts' => $linkedinAccounts,
    'steps' => $steps,
]);
