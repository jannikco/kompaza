<?php

use App\Models\Product;
use App\Models\UpsellOffer;

$tenantId = currentTenantId();
$products = Product::allByTenant($tenantId, 'published');
$upsells = UpsellOffer::allByTenant($tenantId, 'upsell');

view('admin/upsells/form', [
    'tenant' => currentTenant(),
    'offer' => null,
    'products' => $products,
    'upsells' => $upsells,
]);
