<?php

use App\Models\EmailSequence;

if (!isPost()) redirect('/admin/email-sequences');

if (!verifyCsrfToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
    flashMessage('error', 'Invalid CSRF token. Please try again.');
    redirect('/admin/email-sequences');
}

$stepId = $_POST['step_id'] ?? null;
$sequenceId = $_POST['sequence_id'] ?? null;
$tenantId = currentTenantId();

if (!$stepId || !$sequenceId) redirect('/admin/email-sequences');

// Verify the sequence belongs to this tenant
$sequence = EmailSequence::find($sequenceId, $tenantId);
if (!$sequence) {
    flashMessage('error', 'Sequence not found.');
    redirect('/admin/email-sequences');
}

// Verify the step belongs to this sequence
$step = EmailSequence::findStep($stepId);
if (!$step || $step['sequence_id'] != $sequenceId) {
    flashMessage('error', 'Step not found.');
    redirect('/admin/email-sequences/edit?id=' . $sequenceId);
}

$dayNumber = max(0, (int)($_POST['day_number'] ?? 1));
$subject = sanitize($_POST['subject'] ?? '');
$bodyHtml = $_POST['body_html'] ?? '';
$sortOrder = max(0, (int)($_POST['sort_order'] ?? 0));

if (empty($subject)) {
    flashMessage('error', 'Email subject is required.');
    redirect('/admin/email-sequences/edit?id=' . $sequenceId);
}

EmailSequence::updateStep($stepId, [
    'day_number' => $dayNumber,
    'subject' => $subject,
    'body_html' => $bodyHtml,
    'sort_order' => $sortOrder,
]);

logAudit('email_sequence_step_updated', 'email_sequence', $sequenceId);
flashMessage('success', 'Step updated successfully.');
redirect('/admin/email-sequences/edit?id=' . $sequenceId);
