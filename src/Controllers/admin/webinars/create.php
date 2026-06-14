<?php

use App\Models\Product;
use App\Models\EmailSequence;

$tenantId = currentTenantId();
$products = Product::allByTenant($tenantId, 'active');
$emailSequences = EmailSequence::allByTenant($tenantId);

view('admin/webinars/form', [
    'tenant' => currentTenant(),
    'webinar' => null,
    'registrations' => [],
    'products' => $products,
    'emailSequences' => $emailSequences,
]);
