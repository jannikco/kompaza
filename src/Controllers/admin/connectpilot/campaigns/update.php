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

$name = sanitize($_POST['name'] ?? '');
$description = sanitize($_POST['description'] ?? '');
// The form field is `search_url` (see the edit view) and so is the column.
$searchUrl = sanitize($_POST['search_url'] ?? '');
$status = sanitize($_POST['status'] ?? 'draft');
$linkedinAccountId = $_POST['linkedin_account_id'] ?? null;

if (empty($name)) {
    flashMessage('error', 'Campaign name is required.');
    redirect('/admin/connectpilot/kampagner/rediger?id=' . $id);
}

Campaign::update($id, [
    'name' => $name,
    'description' => $description,
    'search_url' => $searchUrl,
    'status' => $status,
    'linkedin_account_id' => $linkedinAccountId ?: null,
]);

// Replace the sequence steps. Table and column names must match
// connectpilot_sequence_steps, which campaigns/store.php already writes.
$db = \App\Database\Database::getConnection();
$stmt = $db->prepare("DELETE FROM connectpilot_sequence_steps WHERE campaign_id = ?");
$stmt->execute([$id]);

$steps = $_POST['steps'] ?? [];
if (!empty($steps) && is_array($steps)) {
    $stmt = $db->prepare("
        INSERT INTO connectpilot_sequence_steps (campaign_id, step_number, action_type, message_template, delay_days, delay_hours, condition_type, created_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
    ");
    foreach ($steps as $index => $step) {
        $stmt->execute([
            $id,
            $index + 1,
            sanitize($step['type'] ?? 'connect'),
            $step['message_template'] ?? '',
            (int)($step['delay_days'] ?? 0),
            (int)($step['delay_hours'] ?? 0),
            sanitize($step['condition'] ?? 'always'),
        ]);
    }
}

logAudit('campaign_updated', 'campaign', $id);
flashMessage('success', 'Campaign updated successfully.');
redirect('/admin/connectpilot/kampagner');
