<?php

use App\Models\ConsultationBooking;

$tenant = currentTenant();
$tenantId = currentTenantId();
$types = ConsultationBooking::getActiveTypes($tenantId);

view('shop/consultation', [
    'tenant' => $tenant,
    'types' => $types,
]);
