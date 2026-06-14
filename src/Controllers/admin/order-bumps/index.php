<?php

use App\Models\OrderBump;

$tenantId = currentTenantId();
$bumps = OrderBump::allByTenant($tenantId);

view('admin/order-bumps/index', [
    'tenant' => currentTenant(),
    'bumps' => $bumps,
]);
