<?php

use App\Models\Order;
use App\Models\UpsellOffer;
use App\Services\StripeService;

$tenant = currentTenant();
$tenantId = currentTenantId();

$orderId = (int)($_GET['order_id'] ?? $_SESSION['pending_order_id'] ?? 0);
$paymentIntentId = $_GET['payment_intent'] ?? null;

if (!$orderId) {
    flashMessage('error', 'No order found.');
    redirect('/');
}

$order = Order::find($orderId, $tenantId);
if (!$order) {
    flashMessage('error', 'Order not found.');
    redirect('/');
}

// Verify payment with Stripe if we have a payment intent
if ($paymentIntentId && $order['status'] === 'awaiting_payment') {
    try {
        $stripe = new StripeService(null, $tenantId);
        $pi = $stripe->retrievePaymentIntent($paymentIntentId);

        if ($pi['status'] === 'succeeded') {
            Order::updateStatus($orderId, 'paid', 'Payment confirmed via Stripe');
        }
    } catch (Exception $e) {
        if (APP_DEBUG) {
            error_log('Payment verification failed: ' . $e->getMessage());
        }
    }
}

// Clear session
unset($_SESSION['pending_order_id'], $_SESSION['stripe_client_secret']);

// Check for upsell offers
$orderItems = \App\Models\OrderItem::allByOrder($orderId);
$cartProductIds = array_map(fn($item) => (int)$item['product_id'], $orderItems);
$upsellOffer = UpsellOffer::findForPurchase($tenantId, $cartProductIds);

if ($upsellOffer) {
    $_SESSION['upsell_order_id'] = $orderId;
    $_SESSION['upsell_offer_id'] = $upsellOffer['id'];
    redirect('/checkout/upsell');
}

flashMessage('success', 'Payment successful! Thank you for your order.');
redirect('/konto/ordrer/' . $orderId);
