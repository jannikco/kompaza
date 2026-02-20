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

// Delete steps, enrollments, and logs first
$db = \App\Database\Database::getConnection();

$stmt = $db->prepare("SELECT id FROM email_sequence_enrollments WHERE sequence_id = ?");
$stmt->execute([$id]);
$enrollmentIds = $stmt->fetchAll(\PDO::FETCH_COLUMN);

if (!empty($enrollmentIds)) {
    $placeholders = implode(',', array_fill(0, count($enrollmentIds), '?'));
    $stmt = $db->prepare("DELETE FROM email_sequence_logs WHERE enrollment_id IN ($placeholders)");
    $stmt->execute($enrollmentIds);
}

$stmt = $db->prepare("DELETE FROM email_sequence_enrollments WHERE sequence_id = ?");
$stmt->execute([$id]);

$stmt = $db->prepare("DELETE FROM email_sequence_steps WHERE sequence_id = ?");
$stmt->execute([$id]);

EmailSequence::delete($id, $tenantId);

logAudit('email_sequence_deleted', 'email_sequence', $id);
flashMessage('success', 'Email sequence deleted successfully.');
redirect('/admin/email-sequences');
