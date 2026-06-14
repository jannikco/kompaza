<?php

use App\Models\Product;
use App\Models\OrderBump;

$tenant = currentTenant();
$tenantId = currentTenantId();

// Load cart from session
$cart = $_SESSION['cart'] ?? [];

if (empty($cart)) {
    flashMessage('error', 'Din kurv er tom.');
    redirect('/kurv');
}

$cartItems = [];
$cartProductIds = [];
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
        $cartProductIds[] = (int)$product['id'];
        $subtotal += $lineTotal;
    }
}

if (empty($cartItems)) {
    flashMessage('error', 'Din kurv er tom.');
    redirect('/kurv');
}

$taxRate = 0.25; // 25% Danish VAT
$tax = round($subtotal * $taxRate, 2);
$total = round($subtotal + $tax, 2);

// Load applicable order bumps
$orderBumps = OrderBump::getApplicable($tenantId, $cartProductIds);

// Check if any product supports payment plans
$hasPaymentPlan = false;
$paymentPlanInfo = null;
foreach ($cartItems as $item) {
    if (!empty($item['product']['payment_plan_enabled']) && count($cartItems) === 1) {
        $hasPaymentPlan = true;
        $product = $item['product'];
        $installmentCount = (int)$product['installment_count'];
        $installmentPrice = $product['installment_price_dkk']
            ? (float)$product['installment_price_dkk']
            : round((float)$product['price_dkk'] / $installmentCount, 2);
        $paymentPlanInfo = [
            'installment_count' => $installmentCount,
            'installment_price' => $installmentPrice,
            'trial_days' => (int)($product['trial_days'] ?? 0),
            'total_plan_price' => $installmentPrice * $installmentCount,
        ];
        break;
    }
}

// Pre-fill form if customer is logged in
$customer = currentUser();

view('shop/checkout', [
    'tenant' => $tenant,
    'cartItems' => $cartItems,
    'subtotal' => $subtotal,
    'tax' => $tax,
    'total' => $total,
    'customer' => $customer,
    'orderBumps' => $orderBumps,
    'hasPaymentPlan' => $hasPaymentPlan,
    'paymentPlanInfo' => $paymentPlanInfo,
]);
