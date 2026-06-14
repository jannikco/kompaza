<?php

use App\Models\UpsellOffer;
use App\Models\Order;

$tenantId = currentTenantId();
$orderId = $_SESSION['upsell_order_id'] ?? null;
$offerId = $_SESSION['upsell_offer_id'] ?? null;

if (!$orderId || !$offerId) {
    redirect('/');
}

$order = Order::find($orderId, $tenantId);
if (!$order) {
    unset($_SESSION['upsell_order_id'], $_SESSION['upsell_offer_id']);
    redirect('/');
}

$offer = UpsellOffer::find($offerId, $tenantId);
if (!$offer || $offer['status'] !== 'active') {
    unset($_SESSION['upsell_order_id'], $_SESSION['upsell_offer_id']);
    flashMessage('success', 'Your order has been placed!');
    redirect('/konto/ordrer/' . $orderId);
}

// Track impression
UpsellOffer::incrementShown($offerId);

view('shop/checkout-upsell', [
    'tenant' => currentTenant(),
    'order' => $order,
    'offer' => $offer,
]);
