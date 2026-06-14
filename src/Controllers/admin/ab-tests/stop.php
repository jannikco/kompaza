<?php

use App\Models\AbTest;
use App\Models\AbTestVariant;

$tenantId = currentTenantId();
$id = (int)($_POST['id'] ?? 0);
$winnerId = !empty($_POST['winner_variant_id']) ? (int)$_POST['winner_variant_id'] : null;

$test = AbTest::find($id, $tenantId);
if (!$test) {
    flashMessage('error', 'A/B test not found.');
    redirect('/admin/ab-tests');
}

AbTest::stop($id, $winnerId);
flashMessage('success', 'A/B test stopped.');
redirect('/admin/ab-tests/edit?id=' . $id);
