<?php

use App\Models\PostAutomation;
use App\Services\LinkedInService;

if (!isPost()) redirect('/admin/connectpilot/automations');

if (!verifyCsrfToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
    flashMessage('error', 'Invalid CSRF token. Please try again.');
    redirect('/admin/connectpilot/automations/create');
}

$tenantId = currentTenantId();

$name = sanitize($_POST['name'] ?? '');
$postUrl = sanitize($_POST['post_url'] ?? '');
$triggerKeyword = sanitize($_POST['trigger_keyword'] ?? '');
$linkedinAccountId = !empty($_POST['linkedin_account_id']) ? (int)$_POST['linkedin_account_id'] : null;
$status = sanitize($_POST['status'] ?? 'active');
$autoReplyEnabled = isset($_POST['auto_reply_enabled']) ? 1 : 0;
$autoReplyTemplate = $_POST['auto_reply_template'] ?? '';
$autoDmEnabled = isset($_POST['auto_dm_enabled']) ? 1 : 0;
$dmTemplate = $_POST['dm_template'] ?? '';
$leadMagnetId = !empty($_POST['lead_magnet_id']) ? (int)$_POST['lead_magnet_id'] : null;

// Validation
if (empty($name) || empty($postUrl) || empty($triggerKeyword) || !$linkedinAccountId) {
    flashMessage('error', 'Name, LinkedIn account, post URL, and trigger keyword are required.');
    redirect('/admin/connectpilot/automations/create');
}

// Extract post URN from URL
$postUrn = LinkedInService::extractPostUrn($postUrl);

// Strip HTML from comment reply (plain text only)
$autoReplyTemplate = strip_tags($autoReplyTemplate);
$dmTemplate = strip_tags($dmTemplate);

$automationId = PostAutomation::create([
    'tenant_id' => $tenantId,
    'linkedin_account_id' => $linkedinAccountId,
    'name' => $name,
    'post_url' => $postUrl,
    'post_urn' => $postUrn,
    'trigger_keyword' => $triggerKeyword,
    'auto_reply_enabled' => $autoReplyEnabled,
    'auto_reply_template' => $autoReplyTemplate,
    'auto_dm_enabled' => $autoDmEnabled,
    'dm_template' => $dmTemplate,
    'lead_magnet_id' => $leadMagnetId,
    'status' => $status,
]);

logAudit('post_automation_created', 'post_automation', $automationId);
flashMessage('success', 'Post automation created successfully.');
redirect('/admin/connectpilot/automations');
