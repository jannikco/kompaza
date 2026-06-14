<?php

use App\Models\UpsellOffer;

$tenantId = currentTenantId();
$offers = UpsellOffer::allByTenant($tenantId);

view('admin/upsells/index', [
    'tenant' => currentTenant(),
    'offers' => $offers,
]);
