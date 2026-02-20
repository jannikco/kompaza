<?php

use App\Models\EmailSequence;

if (!isPost()) redirect('/admin/email-sequences');

if (!verifyCsrfToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
    flashMessage('error', 'Invalid CSRF token. Please try again.');
    redirect('/admin/email-sequences/create');
}

$tenantId = currentTenantId();

$name = sanitize($_POST['name'] ?? '');
$triggerType = sanitize($_POST['trigger_type'] ?? 'manual');
$triggerId = !empty($_POST['trigger_id']) ? (int)$_POST['trigger_id'] : null;
$status = sanitize($_POST['status'] ?? 'draft');

if (empty($name)) {
    flashMessage('error', 'Sequence name is required.');
    redirect('/admin/email-sequences/create');
}

$allowedTriggerTypes = ['manual', 'quiz_completion', 'lead_magnet_signup', 'purchase', 'course_enrollment'];
if (!in_array($triggerType, $allowedTriggerTypes)) {
    $triggerType = 'manual';
}

$allowedStatuses = ['draft', 'active', 'paused'];
if (!in_array($status, $allowedStatuses)) {
    $status = 'draft';
}

$sequenceId = EmailSequence::create([
    'tenant_id' => $tenantId,
    'name' => $name,
    'trigger_type' => $triggerType,
    'trigger_id' => $triggerId,
    'status' => $status,
]);

logAudit('email_sequence_created', 'email_sequence', $sequenceId);
flashMessage('success', 'Email sequence created successfully.');
redirect('/admin/email-sequences/edit?id=' . $sequenceId);
