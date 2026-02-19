<?php

use App\Models\Product;

$tenantId = currentTenantId();
$products = Product::allByTenant($tenantId);

view('admin/products/index', [
    'tenant' => currentTenant(),
    'products' => $products,
]);
