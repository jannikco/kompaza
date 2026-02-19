<?php

use App\Models\Product;

$tenant = currentTenant();
$tenantId = currentTenantId();

// Load cart from session
$cart = $_SESSION['cart'] ?? [];
$cartItems = [];
$subtotal = 0;

foreach ($cart as $productId => $item) {
    $product = Product::find($productId, $tenantId);
    if ($product && $product['status'] === 'published') {
        $quantity = (int)($item['quantity'] ?? 1);
        $lineTotal = (float)$product['price_dkk'] * $quantity;
        $cartItems[] = [
            'product' => $product,
            'quantity' => $quantity,
            'line_total' => $lineTotal,
        ];
        $subtotal += $lineTotal;
    }
}

$taxRate = 0.25; // 25% Danish VAT
$tax = round($subtotal * $taxRate, 2);
$total = round($subtotal + $tax, 2);

view('shop/cart', [
    'tenant' => $tenant,
    'cartItems' => $cartItems,
    'subtotal' => $subtotal,
    'tax' => $tax,
    'total' => $total,
    'itemCount' => count($cartItems),
]);
