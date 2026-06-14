<?php

use App\Models\AbTest;

$tenantId = currentTenantId();
$id = (int)($_POST['id'] ?? 0);

$test = AbTest::find($id, $tenantId);
if (!$test) {
    flashMessage('error', 'A/B test not found.');
    redirect('/admin/ab-tests');
}

if ($test['status'] === 'running') {
    flashMessage('error', 'Cannot delete a running test. Stop it first.');
    redirect('/admin/ab-tests/edit?id=' . $id);
}

AbTest::delete($id, $tenantId);
flashMessage('success', 'A/B test deleted.');
redirect('/admin/ab-tests');
