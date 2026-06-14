<?php

use App\Models\PaymentLink;

$tenantId = currentTenantId();
$links = PaymentLink::allByTenant($tenantId);

view('admin/payment-links/index', [
    'tenant' => currentTenant(),
    'links' => $links,
]);
