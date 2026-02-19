<?php

use App\Models\Campaign;

if (!isPost()) redirect('/admin/leadshark/kampagner');

if (!verifyCsrfToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
    flashMessage('error', 'Invalid CSRF token. Please try again.');
    redirect('/admin/leadshark/kampagner/opret');
}

$tenantId = currentTenantId();

$name = sanitize($_POST['name'] ?? '');
$description = sanitize($_POST['description'] ?? '');
$linkedinSearchUrl = sanitize($_POST['linkedin_search_url'] ?? '');
$status = sanitize($_POST['status'] ?? 'draft');

if (empty($name)) {
    flashMessage('error', 'Campaign name is required.');
    redirect('/admin/leadshark/kampagner/opret');
}

$campaignId = Campaign::create([
    'tenant_id' => $tenantId,
    'name' => $name,
    'description' => $description,
    'linkedin_search_url' => $linkedinSearchUrl,
    'status' => $status,
]);

// Save campaign steps if provided
$steps = $_POST['steps'] ?? [];
if (!empty($steps) && is_array($steps)) {
    $db = \App\Database\Database::getConnection();
    $stmt = $db->prepare("
        INSERT INTO leadshark_campaign_steps (campaign_id, step_order, step_type, message_template, delay_days, delay_hours, step_condition, created_at)
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
redirect('/admin/leadshark/kampagner');
