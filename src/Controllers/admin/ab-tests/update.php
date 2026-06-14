<?php

use App\Models\AbTest;
use App\Models\AbTestVariant;

$tenantId = currentTenantId();
$id = (int)($_POST['id'] ?? 0);

$test = AbTest::find($id, $tenantId);
if (!$test) {
    flashMessage('error', 'A/B test not found.');
    redirect('/admin/ab-tests');
}

$name = trim($_POST['name'] ?? '');
if (!$name) {
    flashMessage('error', 'Name is required.');
    redirect('/admin/ab-tests/edit?id=' . $id);
}

AbTest::update($id, ['name' => $name]);

// Only update variants if test is not running
if ($test['status'] !== 'running') {
    AbTestVariant::deleteByTest($id);

    $originalType = $_POST['original_type'] ?? $test['original_type'];
    $originalId = (int)($_POST['original_id'] ?? $test['original_id']);

    AbTest::update($id, [
        'original_type' => $originalType,
        'original_id' => $originalId,
    ]);

    // Recreate control
    AbTestVariant::create([
        'ab_test_id' => $id,
        'name' => 'Original (Control)',
        'variant_type' => $originalType,
        'variant_id' => $originalId,
        'traffic_weight' => (int)($_POST['control_weight'] ?? 50),
        'is_control' => true,
    ]);

    // Recreate challengers
    $variantNames = $_POST['variant_name'] ?? [];
    $variantIds = $_POST['variant_page_id'] ?? [];
    $variantWeights = $_POST['variant_weight'] ?? [];

    foreach ($variantNames as $i => $vName) {
        if (empty(trim($vName)) || empty($variantIds[$i])) continue;
        AbTestVariant::create([
            'ab_test_id' => $id,
            'name' => trim($vName),
            'variant_type' => $originalType,
            'variant_id' => (int)$variantIds[$i],
            'traffic_weight' => (int)($variantWeights[$i] ?? 50),
            'is_control' => false,
        ]);
    }
}

flashMessage('success', 'A/B test updated.');
redirect('/admin/ab-tests/edit?id=' . $id);
