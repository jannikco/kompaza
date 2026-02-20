<?php

use App\Models\MastermindProgram;

if (!isPost()) redirect('/admin/mastermind');

if (!verifyCsrfToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
    flashMessage('error', 'Invalid CSRF token. Please try again.');
    redirect('/admin/mastermind');
}

$programId = $_POST['program_id'] ?? null;
$tenantId = currentTenantId();

if (!$programId) redirect('/admin/mastermind');

$program = MastermindProgram::find($programId, $tenantId);
if (!$program) {
    flashMessage('error', 'Program not found.');
    redirect('/admin/mastermind');
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

// Get next sort order
$tiers = MastermindProgram::getTiers($programId);
$sortOrder = count($tiers);

MastermindProgram::createTier([
    'program_id' => $programId,
    'name' => $name,
    'description' => $description,
    'upfront_price_dkk' => $upfrontPriceDkk,
    'monthly_price_dkk' => $monthlyPriceDkk,
    'max_members' => $maxMembers,
    'stripe_price_id' => null,
    'features' => $featuresRaw,
    'sort_order' => $sortOrder,
]);

logAudit('tier_created', 'mastermind_program', $programId, ['tier_name' => $name]);
flashMessage('success', 'Tier added successfully.');
redirect('/admin/mastermind/edit?id=' . $programId);
