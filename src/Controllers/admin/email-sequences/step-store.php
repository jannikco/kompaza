<?php

use App\Models\EmailSequence;

if (!isPost()) redirect('/admin/email-sequences');

if (!verifyCsrfToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
    flashMessage('error', 'Invalid CSRF token. Please try again.');
    redirect('/admin/email-sequences');
}

$sequenceId = $_POST['sequence_id'] ?? null;
$tenantId = currentTenantId();

if (!$sequenceId) redirect('/admin/email-sequences');

$sequence = EmailSequence::find($sequenceId, $tenantId);
if (!$sequence) {
    flashMessage('error', 'Sequence not found.');
    redirect('/admin/email-sequences');
}

$dayNumber = max(0, (int)($_POST['day_number'] ?? 1));
$subject = sanitize($_POST['subject'] ?? '');
$bodyHtml = $_POST['body_html'] ?? '';
$sortOrder = max(0, (int)($_POST['sort_order'] ?? 0));

if (empty($subject)) {
    flashMessage('error', 'Email subject is required.');
    redirect('/admin/email-sequences/edit?id=' . $sequenceId);
}

// Auto-calculate sort_order if not provided
if ($sortOrder === 0) {
    $existingSteps = EmailSequence::getSteps($sequenceId);
    $sortOrder = count($existingSteps);
}

EmailSequence::createStep([
    'sequence_id' => $sequenceId,
    'day_number' => $dayNumber,
    'subject' => $subject,
    'body_html' => $bodyHtml,
    'sort_order' => $sortOrder,
]);

logAudit('email_sequence_step_created', 'email_sequence', $sequenceId);
flashMessage('success', 'Step added successfully.');
redirect('/admin/email-sequences/edit?id=' . $sequenceId);
