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

$input = PostAutomation::fromForm($_POST);
$input['post_urn'] = LinkedInService::extractPostUrn($input['post_url']);

if ($input['name'] === '' || $input['post_url'] === '' || $input['trigger_keyword'] === '' || !$input['linkedin_account_id']) {
    flashMessage('error', 'Name, LinkedIn account, post URL, and at least one trigger keyword are required.');
    redirect('/admin/connectpilot/automations/edit?id=' . $id);
}

PostAutomation::update($id, $input);

logAudit('post_automation_updated', 'post_automation', $id);
flashMessage('success', 'Post automation updated successfully.');
redirect('/admin/connectpilot/automations');
