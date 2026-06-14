<?php

use App\Models\OrderBump;
use App\Models\Product;

$id = $_GET['id'] ?? null;
$tenantId = currentTenantId();

if (!$id) { redirect('/admin/order-bumps'); }

$bump = OrderBump::find($id, $tenantId);
if (!$bump) {
    flashMessage('error', 'Order bump not found.');
    redirect('/admin/order-bumps');
}

$products = Product::allByTenant($tenantId, 'published');

view('admin/order-bumps/form', [
    'tenant' => currentTenant(),
    'bump' => $bump,
    'products' => $products,
]);
