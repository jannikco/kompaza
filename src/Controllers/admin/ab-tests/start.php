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

$variants = AbTestVariant::allByTest($id);
if (count($variants) < 2) {
    flashMessage('error', 'You need at least 2 variants to start a test.');
    redirect('/admin/ab-tests/edit?id=' . $id);
}

AbTest::start($id);
flashMessage('success', 'A/B test started! Traffic is now being split between variants.');
redirect('/admin/ab-tests/edit?id=' . $id);
