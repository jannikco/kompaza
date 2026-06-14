<?php

use App\Models\PaymentLink;
use App\Models\Product;

$id = $_GET['id'] ?? null;
$tenantId = currentTenantId();

if (!$id) { redirect('/admin/payment-links'); }

$link = PaymentLink::find($id, $tenantId);
if (!$link) {
    flashMessage('error', 'Payment link not found.');
    redirect('/admin/payment-links');
}

$products = Product::allByTenant($tenantId, 'published');

view('admin/payment-links/form', [
    'tenant' => currentTenant(),
    'link' => $link,
    'products' => $products,
]);
