<?php

use App\Models\Product;

$slug = $dynamicParams['slug'] ?? $slug ?? null;
$tenantId = currentTenantId();

if (!$slug) {
    http_response_code(404);
    view('errors/404');
    exit;
}

$product = Product::findBySlug($slug, $tenantId);
if (!$product) {
    http_response_code(404);
    view('errors/404');
    exit;
}

// Direct buy: add to cart as single item and redirect to checkout
$_SESSION['cart'] = [
    $product['id'] => [
        'product_id' => $product['id'],
        'name' => $product['name'],
        'price' => $product['price_dkk'],
        'quantity' => 1,
        'image' => $product['image_path'] ?? '',
    ],
];

redirect('/checkout');
