<?php

use App\Models\Funnel;

$tenantId = currentTenantId();
$funnels = Funnel::getWithStats($tenantId);

view('admin/funnels/index', [
    'tenant' => currentTenant(),
    'funnels' => $funnels,
]);
