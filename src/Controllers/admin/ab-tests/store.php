<?php

use App\Models\AbTest;
use App\Models\AbTestVariant;

$tenantId = currentTenantId();

$name = trim($_POST['name'] ?? '');
$testType = $_POST['test_type'] ?? 'landing_page';
$originalType = $_POST['original_type'] ?? '';
$originalId = (int)($_POST['original_id'] ?? 0);

if (!$name || !$originalType || !$originalId) {
    flashMessage('error', 'Name and original page are required.');
    redirect('/admin/ab-tests/create');
}

$testId = AbTest::create([
    'tenant_id' => $tenantId,
    'name' => $name,
    'test_type' => $testType,
    'original_type' => $originalType,
    'original_id' => $originalId,
]);

// Create control variant (the original)
AbTestVariant::create([
    'ab_test_id' => $testId,
    'name' => 'Original (Control)',
    'variant_type' => $originalType,
    'variant_id' => $originalId,
    'traffic_weight' => 50,
    'is_control' => true,
]);

// Create challenger variants
$variantNames = $_POST['variant_name'] ?? [];
$variantIds = $_POST['variant_page_id'] ?? [];
$variantWeights = $_POST['variant_weight'] ?? [];

foreach ($variantNames as $i => $vName) {
    if (empty(trim($vName)) || empty($variantIds[$i])) continue;
    AbTestVariant::create([
        'ab_test_id' => $testId,
        'name' => trim($vName),
        'variant_type' => $originalType,
        'variant_id' => (int)$variantIds[$i],
        'traffic_weight' => (int)($variantWeights[$i] ?? 50),
        'is_control' => false,
    ]);
}

flashMessage('success', 'A/B test created. Add variants and start the test when ready.');
redirect('/admin/ab-tests/edit?id=' . $testId);
