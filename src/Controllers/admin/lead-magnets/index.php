<?php

use App\Models\LeadMagnet;

$tenantId = currentTenantId();
$leadMagnets = LeadMagnet::allByTenant($tenantId);

view('admin/lead-magnets/index', [
    'tenant' => currentTenant(),
    'leadMagnets' => $leadMagnets,
]);
