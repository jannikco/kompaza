<?php

use App\Models\Campaign;

if (!isPost()) redirect('/admin/connectpilot/kampagner');

if (!verifyCsrfToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
    flashMessage('error', 'Invalid CSRF token. Please try again.');
    redirect('/admin/connectpilot/kampagner');
}

$id = $_POST['id'] ?? null;
$tenantId = currentTenantId();

if (!$id) redirect('/admin/connectpilot/kampagner');

$campaign = Campaign::find($id, $tenantId);
if (!$campaign) {
    flashMessage('error', 'Campaign not found.');
    redirect('/admin/connectpilot/kampagner');
}

// connectpilot_sequence_steps has ON DELETE CASCADE on campaign_id, so removing
// the campaign clears its steps. The explicit delete stays as a belt-and-braces
// for any row orphaned before the FK existed.
$db = \App\Database\Database::getConnection();
$stmt = $db->prepare("DELETE FROM connectpilot_sequence_steps WHERE campaign_id = ?");
$stmt->execute([$id]);

Campaign::delete($id, $tenantId);

logAudit('campaign_deleted', 'campaign', $id);
flashMessage('success', 'Campaign deleted successfully.');
redirect('/admin/connectpilot/kampagner');
