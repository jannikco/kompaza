<?php

use App\Models\ConsultationBooking;

$tenantId = currentTenantId();
$types = ConsultationBooking::allTypes($tenantId);

view('admin/consultations/types', [
    'tenant' => currentTenant(),
    'types' => $types,
]);
