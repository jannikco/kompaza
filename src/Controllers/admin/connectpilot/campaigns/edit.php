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

// Load campaign steps. The table is `connectpilot_sequence_steps` (campaigns/store.php
// has always written that one); the view speaks the older step_order/step_type/
// step_condition names, so alias rather than touch the view.
$stmt = $db->prepare("
    SELECT *,
           step_number    AS step_order,
           action_type    AS step_type,
           condition_type AS step_condition
      FROM connectpilot_sequence_steps
     WHERE campaign_id = ?
     ORDER BY step_number ASC
");
$stmt->execute([$id]);
$steps = $stmt->fetchAll();

view('admin/connectpilot/campaigns/edit', [
    'tenant' => currentTenant(),
    'campaign' => $campaign,
    'linkedinAccounts' => $linkedinAccounts,
    'steps' => $steps,
]);
