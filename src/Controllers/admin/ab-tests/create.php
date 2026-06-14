<?php

use App\Models\LeadMagnet;
use App\Models\Product;

$tenantId = currentTenantId();

$leadMagnets = LeadMagnet::allByTenant($tenantId);
$products = Product::allByTenant($tenantId);

view('admin/ab-tests/form', [
    'tenant' => currentTenant(),
    'test' => null,
    'variants' => [],
    'leadMagnets' => $leadMagnets,
    'products' => $products,
]);
