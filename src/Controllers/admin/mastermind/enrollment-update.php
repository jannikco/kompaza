<?php

use App\Models\MastermindProgram;

if (!isPost()) redirect('/admin/mastermind');

if (!verifyCsrfToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
    flashMessage('error', 'Invalid CSRF token. Please try again.');
    redirect('/admin/mastermind');
}

$enrollmentId = $_POST['enrollment_id'] ?? null;
$programId = $_POST['program_id'] ?? null;
$status = sanitize($_POST['status'] ?? '');
$tenantId = currentTenantId();

if (!$enrollmentId || !$programId || !$status) redirect('/admin/mastermind');

$program = MastermindProgram::find($programId, $tenantId);
if (!$program) {
    flashMessage('error', 'Program not found.');
    redirect('/admin/mastermind');
}

$enrollment = MastermindProgram::findEnrollment($enrollmentId);
if (!$enrollment || $enrollment['program_id'] != $programId) {
    flashMessage('error', 'Enrollment not found.');
    redirect('/admin/mastermind/edit?id=' . $programId);
}

$validStatuses = ['active', 'paused', 'cancelled', 'completed'];
if (!in_array($status, $validStatuses)) {
    flashMessage('error', 'Invalid status.');
    redirect('/admin/mastermind/edit?id=' . $programId);
}

$updateData = ['status' => $status];
if ($status === 'cancelled') {
    $updateData['cancelled_at'] = date('Y-m-d H:i:s');
}

MastermindProgram::updateEnrollment($enrollmentId, $updateData);

logAudit('enrollment_updated', 'mastermind_enrollment', $enrollmentId, ['status' => $status]);
flashMessage('success', 'Enrollment status updated to ' . ucfirst($status) . '.');
redirect('/admin/mastermind/edit?id=' . $programId);
