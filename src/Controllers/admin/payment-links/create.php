<?php

use App\Models\Product;

$tenantId = currentTenantId();
$products = Product::allByTenant($tenantId, 'published');

view('admin/payment-links/form', [
    'tenant' => currentTenant(),
    'link' => null,
    'products' => $products,
]);
