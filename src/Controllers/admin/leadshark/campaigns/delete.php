<?php

use App\Models\Campaign;

if (!isPost()) redirect('/admin/leadshark/kampagner');

if (!verifyCsrfToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
    flashMessage('error', 'Invalid CSRF token. Please try again.');
    redirect('/admin/leadshark/kampagner');
}

$id = $_POST['id'] ?? null;
$tenantId = currentTenantId();

if (!$id) redirect('/admin/leadshark/kampagner');

$campaign = Campaign::find($id, $tenantId);
if (!$campaign) {
    flashMessage('error', 'Campaign not found.');
    redirect('/admin/leadshark/kampagner');
}

// Delete campaign steps first
$db = \App\Database\Database::getConnection();
$stmt = $db->prepare("DELETE FROM leadshark_campaign_steps WHERE campaign_id = ?");
$stmt->execute([$id]);

Campaign::delete($id, $tenantId);

logAudit('campaign_deleted', 'campaign', $id);
flashMessage('success', 'Campaign deleted successfully.');
redirect('/admin/leadshark/kampagner');
