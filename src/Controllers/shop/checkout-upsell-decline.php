<?php

use App\Models\UpsellOffer;

if (!isPost()) redirect('/');

$tenantId = currentTenantId();
$orderId = $_SESSION['upsell_order_id'] ?? null;
$offerId = (int)($_POST['offer_id'] ?? $_SESSION['upsell_offer_id'] ?? 0);

if (!$orderId) {
    redirect('/');
}

// Check for downsell
$downsell = null;
if ($offerId) {
    $downsell = UpsellOffer::findDownsell($offerId, $tenantId);
}

if ($downsell) {
    // Show downsell instead
    $_SESSION['upsell_offer_id'] = $downsell['id'];
    redirect('/checkout/upsell');
}

// No downsell - go to thank-you
unset($_SESSION['upsell_order_id'], $_SESSION['upsell_offer_id']);
flashMessage('success', 'Your order has been placed!');
redirect('/konto/ordrer/' . $orderId);
