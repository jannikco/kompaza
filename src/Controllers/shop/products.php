<?php

use App\Models\Product;

$tenant = currentTenant();
$tenantId = currentTenantId();

$products = Product::publishedByTenant($tenantId);

view('shop/products', [
    'tenant' => $tenant,
    'products' => $products,
]);
