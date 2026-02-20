<?php

use App\Models\EmailSequence;

if (!isPost()) redirect('/admin/email-sequences');

if (!verifyCsrfToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
    flashMessage('error', 'Invalid CSRF token. Please try again.');
    redirect('/admin/email-sequences');
}

$id = $_POST['id'] ?? null;
$tenantId = currentTenantId();

if (!$id) redirect('/admin/email-sequences');

$sequence = EmailSequence::find($id, $tenantId);
if (!$sequence) {
    flashMessage('error', 'Sequence not found.');
    redirect('/admin/email-sequences');
}

$name = sanitize($_POST['name'] ?? '');
$triggerType = sanitize($_POST['trigger_type'] ?? 'manual');
$triggerId = !empty($_POST['trigger_id']) ? (int)$_POST['trigger_id'] : null;
$status = sanitize($_POST['status'] ?? 'draft');

if (empty($name)) {
    flashMessage('error', 'Sequence name is required.');
    redirect('/admin/email-sequences/edit?id=' . $id);
}

$allowedTriggerTypes = ['manual', 'quiz_completion', 'lead_magnet_signup', 'purchase', 'course_enrollment'];
if (!in_array($triggerType, $allowedTriggerTypes)) {
    $triggerType = 'manual';
}

$allowedStatuses = ['draft', 'active', 'paused'];
if (!in_array($status, $allowedStatuses)) {
    $status = 'draft';
}

EmailSequence::update($id, [
    'name' => $name,
    'trigger_type' => $triggerType,
    'trigger_id' => $triggerId,
    'status' => $status,
]);

logAudit('email_sequence_updated', 'email_sequence', $id);
flashMessage('success', 'Email sequence updated successfully.');
redirect('/admin/email-sequences/edit?id=' . $id);
