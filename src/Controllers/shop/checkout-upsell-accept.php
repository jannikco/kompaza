<?php

use App\Models\UpsellOffer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;

if (!isPost()) redirect('/');

$tenantId = currentTenantId();
$orderId = $_SESSION['upsell_order_id'] ?? null;
$offerId = (int)($_POST['offer_id'] ?? $_SESSION['upsell_offer_id'] ?? 0);

if (!$orderId || !$offerId) {
    redirect('/');
}

$order = Order::find($orderId, $tenantId);
$offer = UpsellOffer::find($offerId, $tenantId);

if (!$order || !$offer) {
    unset($_SESSION['upsell_order_id'], $_SESSION['upsell_offer_id']);
    redirect('/');
}

$product = Product::find($offer['product_id'], $tenantId);
if (!$product) {
    unset($_SESSION['upsell_order_id'], $_SESSION['upsell_offer_id']);
    flashMessage('success', 'Your order has been placed!');
    redirect('/konto/ordrer/' . $orderId);
}

// Add upsell item to the order
$upsellPrice = (float)$offer['offer_price_dkk'];
$taxRate = 0.25;
$upsellTax = round($upsellPrice * $taxRate, 2);
$upsellTotal = round($upsellPrice + $upsellTax, 2);

// For card orders, charge the upsell off-session against the card saved at checkout.
// If the charge can't be made (no saved method, SCA required, decline), keep the
// original order intact and do not grant the upsell.
if (($order['payment_method'] ?? '') === 'card' && !empty($order['payment_reference'])) {
    try {
        $stripe = new \App\Services\StripeService(null, $tenantId);
        if (!$stripe->isConfigured()) {
            throw new \Exception('Stripe not configured');
        }
        $origPi = $stripe->retrievePaymentIntent($order['payment_reference']);
        $customerId = $origPi['customer'] ?? null;
        $pmId = $origPi['payment_method'] ?? null;
        if (!$customerId || !$pmId) {
            throw new \Exception('No saved payment method available for upsell');
        }
        $stripe->chargeOffSession((int)round($upsellTotal * 100), 'dkk', $customerId, $pmId, [
            'order_id' => $orderId,
            'type' => 'upsell',
            'upsell_offer_id' => $offerId,
        ]);
    } catch (\Exception $e) {
        if (APP_DEBUG) {
            error_log('Upsell off-session charge failed: ' . $e->getMessage());
        }
        unset($_SESSION['upsell_order_id'], $_SESSION['upsell_offer_id']);
        flashMessage('error', 'We could not process the additional offer, so your original order stands.');
        redirect('/konto/ordrer/' . $orderId);
    }
}

OrderItem::create([
    'order_id' => $orderId,
    'product_id' => $product['id'],
    'product_name' => $product['name'],
    'product_sku' => $product['sku'] ?? null,
    'quantity' => 1,
    'unit_price_dkk' => $upsellPrice,
    'total_price_dkk' => $upsellPrice,
    'is_digital' => $product['is_digital'] ?? 0,
    'digital_file_path' => $product['digital_file_path'] ?? null,
    'source' => 'upsell',
    'upsell_offer_id' => $offerId,
]);

// Update order totals
$newSubtotal = (float)$order['subtotal_dkk'] + $upsellPrice;
$newTax = round($newSubtotal * $taxRate, 2);
$newTotal = round($newSubtotal + $newTax, 2);

Order::update($orderId, [
    'subtotal_dkk' => $newSubtotal,
    'tax_dkk' => $newTax,
    'total_dkk' => $newTotal,
    'has_upsells' => 1,
]);

// Track acceptance
UpsellOffer::incrementAccepted($offerId);

logAudit('upsell_accepted', 'upsell_offer', $offerId, [
    'order_id' => $orderId,
    'offer_price_dkk' => $upsellPrice,
]);

// Clean up session
unset($_SESSION['upsell_order_id'], $_SESSION['upsell_offer_id']);

flashMessage('success', 'Your order has been placed with the additional offer!');
redirect('/konto/ordrer/' . $orderId);
