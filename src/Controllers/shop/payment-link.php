<?php

use App\Models\PaymentLink;
use App\Models\Product;

$token = $dynamicParams['token'] ?? $token ?? null;
if (!$token) {
    http_response_code(404);
    view('errors/404');
    exit;
}

$link = PaymentLink::findByToken($token);
if (!$link || !PaymentLink::isValid($link)) {
    http_response_code(404);
    view('errors/404');
    exit;
}

$product = Product::find($link['product_id'], $link['tenant_id']);
if (!$product || $product['status'] !== 'published') {
    http_response_code(404);
    view('errors/404');
    exit;
}

$price = $link['custom_price_dkk'] ?? $product['price_dkk'];
$productName = $link['custom_name'] ?? $product['name'];

view('shop/payment-link', [
    'tenant' => currentTenant(),
    'link' => $link,
    'product' => $product,
    'price' => $price,
    'productName' => $productName,
]);
