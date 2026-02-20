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

$name = sanitize($_POST['name'] ?? '');
$description = sanitize($_POST['description'] ?? '');
$upfrontPriceDkk = (float)($_POST['upfront_price_dkk'] ?? 0);
$monthlyPriceDkk = (float)($_POST['monthly_price_dkk'] ?? 0);
$maxMembers = !empty($_POST['max_members']) ? (int)$_POST['max_members'] : null;
$featuresRaw = $_POST['features'] ?? '';

if (empty($name)) {
    flashMessage('error', 'Tier name is required.');
    redirect('/admin/mastermind/edit?id=' . $programId);
}

MastermindProgram::updateTier($tierId, [
    'name' => $name,
    'description' => $description,
    'upfront_price_dkk' => $upfrontPriceDkk,
    'monthly_price_dkk' => $monthlyPriceDkk,
    'max_members' => $maxMembers,
    'features' => $featuresRaw,
]);

logAudit('tier_updated', 'mastermind_program', $programId, ['tier_id' => $tierId]);
flashMessage('success', 'Tier updated successfully.');
redirect('/admin/mastermind/edit?id=' . $programId);
