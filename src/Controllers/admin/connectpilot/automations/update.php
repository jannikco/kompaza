<?php

use App\Models\PostAutomation;
use App\Services\LinkedInService;

if (!isPost()) redirect('/admin/connectpilot/automations');

if (!verifyCsrfToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
    flashMessage('error', 'Invalid CSRF token. Please try again.');
    redirect('/admin/connectpilot/automations');
}

$id = $_POST['id'] ?? null;
$tenantId = currentTenantId();

if (!$id) redirect('/admin/connectpilot/automations');

$automation = PostAutomation::find($id, $tenantId);
if (!$automation) {
    flashMessage('error', 'Automation not found.');
    redirect('/admin/connectpilot/automations');
}

$name = sanitize($_POST['name'] ?? '');
$postUrl = sanitize($_POST['post_url'] ?? '');
$triggerKeyword = sanitize($_POST['trigger_keyword'] ?? '');
$linkedinAccountId = !empty($_POST['linkedin_account_id']) ? (int)$_POST['linkedin_account_id'] : null;
$status = sanitize($_POST['status'] ?? 'active');
$autoReplyEnabled = isset($_POST['auto_reply_enabled']) ? 1 : 0;
$autoReplyTemplate = strip_tags($_POST['auto_reply_template'] ?? '');
$autoDmEnabled = isset($_POST['auto_dm_enabled']) ? 1 : 0;
$dmTemplate = strip_tags($_POST['dm_template'] ?? '');
$leadMagnetId = !empty($_POST['lead_magnet_id']) ? (int)$_POST['lead_magnet_id'] : null;

if (empty($name) || empty($postUrl) || empty($triggerKeyword) || !$linkedinAccountId) {
    flashMessage('error', 'Name, LinkedIn account, post URL, and trigger keyword are required.');
    redirect('/admin/connectpilot/automations/edit?id=' . $id);
}

$postUrn = LinkedInService::extractPostUrn($postUrl);

PostAutomation::update($id, [
    'name' => $name,
    'linkedin_account_id' => $linkedinAccountId,
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

logAudit('post_automation_updated', 'post_automation', $id);
flashMessage('success', 'Post automation updated successfully.');
redirect('/admin/connectpilot/automations');
