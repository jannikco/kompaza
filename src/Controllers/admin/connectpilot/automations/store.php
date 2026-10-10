<?php

use App\Models\PostAutomation;
use App\Services\LinkedInService;

if (!isPost()) redirect('/admin/connectpilot/automations');

if (!verifyCsrfToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
    flashMessage('error', 'Invalid CSRF token. Please try again.');
    redirect('/admin/connectpilot/automations/create');
}

$tenantId = currentTenantId();
$input = PostAutomation::fromForm($_POST);
$input['post_urn'] = LinkedInService::extractPostUrn($input['post_url']);

if ($input['name'] === '' || $input['post_url'] === '' || $input['trigger_keyword'] === '' || !$input['linkedin_account_id']) {
    flashMessage('error', 'Name, LinkedIn account, post URL, and at least one trigger keyword are required.');
    redirect('/admin/connectpilot/automations/create');
}

$automationId = PostAutomation::create($input + ['tenant_id' => $tenantId]);

logAudit('post_automation_created', 'post_automation', $automationId);
$label = $input['status'] === 'active' ? 'Post automation created and running.' : 'Post automation saved as ' . $input['status'] . '.';
flashMessage('success', $label);
redirect('/admin/connectpilot/automations');
