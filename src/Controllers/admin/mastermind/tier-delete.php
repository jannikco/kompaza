<?php

use App\Models\MastermindProgram;

if (!isPost()) redirect('/admin/mastermind');

if (!verifyCsrfToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
    flashMessage('error', 'Invalid CSRF token. Please try again.');
    redirect('/admin/mastermind');
}

$tierId = $_POST['tier_id'] ?? null;
$programId = $_POST['program_id'] ?? null;
$tenantId = currentTenantId();

if (!$tierId || !$programId) redirect('/admin/mastermind');

$program = MastermindProgram::find($programId, $tenantId);
if (!$program) {
    flashMessage('error', 'Program not found.');
    redirect('/admin/mastermind');
}

$tier = MastermindProgram::findTier($tierId);
if (!$tier || $tier['program_id'] != $programId) {
    flashMessage('error', 'Tier not found.');
    redirect('/admin/mastermind/edit?id=' . $programId);
}

MastermindProgram::deleteTier($tierId);

logAudit('tier_deleted', 'mastermind_program', $programId, ['tier_id' => $tierId]);
flashMessage('success', 'Tier deleted successfully.');
redirect('/admin/mastermind/edit?id=' . $programId);
