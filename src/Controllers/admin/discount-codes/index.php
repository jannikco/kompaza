<?php

use App\Models\DiscountCode;

$tenantId = currentTenantId();
$discountCodes = DiscountCode::allByTenant($tenantId);

view('admin/discount-codes/index', [
    'tenant' => currentTenant(),
    'discountCodes' => $discountCodes,
]);
