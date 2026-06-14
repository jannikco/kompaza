<?php

use App\Models\Order;
use App\Services\StripeService;

$tenant = currentTenant();
$tenantId = currentTenantId();

$orderId = $_SESSION['pending_order_id'] ?? null;
$clientSecret = $_SESSION['stripe_client_secret'] ?? null;

if (!$orderId || !$clientSecret) {
    flashMessage('error', 'No pending payment found.');
    redirect('/checkout');
}

$order = Order::find($orderId, $tenantId);
if (!$order || $order['status'] !== 'awaiting_payment') {
    unset($_SESSION['pending_order_id'], $_SESSION['stripe_client_secret']);
    flashMessage('error', 'Order not found or already paid.');
    redirect('/checkout');
}

$stripePublishableKey = StripeService::getPublishableKey($tenantId);

view('shop/checkout-payment', [
    'tenant' => $tenant,
    'order' => $order,
    'clientSecret' => $clientSecret,
    'stripePublishableKey' => $stripePublishableKey,
]);
