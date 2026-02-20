<?php

use App\Models\MastermindProgram;

if (!isPost()) redirect('/admin/mastermind');

if (!verifyCsrfToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
    flashMessage('error', 'Invalid CSRF token. Please try again.');
    redirect('/admin/mastermind');
}

$id = $_POST['id'] ?? null;
$tenantId = currentTenantId();

if (!$id) redirect('/admin/mastermind');

$program = MastermindProgram::find($id, $tenantId);
if (!$program) {
    flashMessage('error', 'Program not found.');
    redirect('/admin/mastermind');
}

// Delete cover image
if (!empty($program['cover_image_path'])) {
    deleteUploadedFile($program['cover_image_path']);
}

// Delete tiers first
$tiers = MastermindProgram::getTiers($id);
foreach ($tiers as $tier) {
    MastermindProgram::deleteTier($tier['id']);
}

MastermindProgram::delete($id, $tenantId);

logAudit('mastermind_deleted', 'mastermind_program', $id);
flashMessage('success', 'Mastermind program deleted successfully.');
redirect('/admin/mastermind');
