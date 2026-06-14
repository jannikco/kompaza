<?php

use App\Models\AbTest;

$tenantId = currentTenantId();
$tests = AbTest::getWithStats($tenantId);

view('admin/ab-tests/index', [
    'tenant' => currentTenant(),
    'tests' => $tests,
]);
