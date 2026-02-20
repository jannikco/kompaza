<?php

use App\Models\Campaign;

if (!isPost()) redirect('/admin/connectpilot/kampagner');

if (!verifyCsrfToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
    flashMessage('error', 'Invalid CSRF token. Please try again.');
    redirect('/admin/connectpilot/kampagner/opret');
}

$tenantId = currentTenantId();

$name = sanitize($_POST['name'] ?? '');
$description = sanitize($_POST['description'] ?? '');
$searchUrl = sanitize($_POST['search_url'] ?? '');
$linkedinAccountId = !empty($_POST['linkedin_account_id']) ? (int)$_POST['linkedin_account_id'] : null;
$status = sanitize($_POST['status'] ?? 'draft');

if (empty($name)) {
    flashMessage('error', 'Campaign name is required.');
    redirect('/admin/connectpilot/kampagner/opret');
}

$campaignId = Campaign::create([
    'tenant_id' => $tenantId,
    'name' => $name,
    'description' => $description,
    'linkedin_account_id' => $linkedinAccountId,
    'search_url' => $searchUrl,
    'status' => $status,
]);

// Save sequence steps if provided
$steps = $_POST['steps'] ?? [];
if (!empty($steps) && is_array($steps)) {
    $db = \App\Database\Database::getConnection();
    $stmt = $db->prepare("
        INSERT INTO connectpilot_sequence_steps (campaign_id, step_number, action_type, message_template, delay_days, delay_hours, condition_type, created_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
    ");
    foreach ($steps as $index => $step) {
        $stmt->execute([
            $campaignId,
            $index + 1,
            sanitize($step['type'] ?? 'connect'),
            $step['message_template'] ?? '',
            (int)($step['delay_days'] ?? 0),
            (int)($step['delay_hours'] ?? 0),
            sanitize($step['condition'] ?? 'always'),
        ]);
    }
}

logAudit('campaign_created', 'campaign', $campaignId);
flashMessage('success', 'Campaign created successfully.');
redirect('/admin/connectpilot/kampagner');
