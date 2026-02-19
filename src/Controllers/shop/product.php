<?php

use App\Models\Product;

$tenant = currentTenant();
$tenantId = currentTenantId();

$product = Product::findBySlug($slug, $tenantId);

if (!$product) {
    http_response_code(404);
    view('errors/404');
    exit;
}

Product::incrementViews($product['id']);

view('shop/product', [
    'tenant' => $tenant,
    'product' => $product,
]);
