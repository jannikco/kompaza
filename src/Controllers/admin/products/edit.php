<?php

use App\Models\Product;

$id = $_GET['id'] ?? null;
$tenantId = currentTenantId();

if (!$id) {
    http_response_code(404);
    view('errors/404');
    exit;
}

$product = Product::find($id, $tenantId);

if (!$product) {
    http_response_code(404);
    view('errors/404');
    exit;
}

view('admin/products/edit', [
    'tenant' => currentTenant(),
    'product' => $product,
]);
