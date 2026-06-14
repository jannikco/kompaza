<?php

use App\Models\UpsellOffer;
use App\Models\Product;

$id = $_GET['id'] ?? null;
$tenantId = currentTenantId();

if (!$id) { redirect('/admin/upsells'); }

$offer = UpsellOffer::find($id, $tenantId);
if (!$offer) {
    flashMessage('error', 'Offer not found.');
    redirect('/admin/upsells');
}

$products = Product::allByTenant($tenantId, 'published');
$upsells = UpsellOffer::allByTenant($tenantId, 'upsell');

view('admin/upsells/form', [
    'tenant' => currentTenant(),
    'offer' => $offer,
    'products' => $products,
    'upsells' => $upsells,
]);
