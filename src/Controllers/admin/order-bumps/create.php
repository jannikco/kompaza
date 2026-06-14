<?php

use App\Models\Product;

$tenantId = currentTenantId();
$products = Product::allByTenant($tenantId, 'published');

view('admin/order-bumps/form', [
    'tenant' => currentTenant(),
    'bump' => null,
    'products' => $products,
]);
