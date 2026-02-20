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

// Delete any logs referencing this step
$db = \App\Database\Database::getConnection();
$stmt = $db->prepare("DELETE FROM email_sequence_logs WHERE step_id = ?");
$stmt->execute([$stepId]);

EmailSequence::deleteStep($stepId);

logAudit('email_sequence_step_deleted', 'email_sequence', $sequenceId);
flashMessage('success', 'Step deleted successfully.');
redirect('/admin/email-sequences/edit?id=' . $sequenceId);
