<?php

use App\Models\AbTest;
use App\Models\AbTestVariant;
use App\Models\LeadMagnet;
use App\Models\Product;

$tenantId = currentTenantId();
$id = (int)($_GET['id'] ?? 0);

$test = AbTest::find($id, $tenantId);
if (!$test) {
    flashMessage('error', 'A/B test not found.');
    redirect('/admin/ab-tests');
}

$variants = AbTestVariant::allByTest($id);
$leadMagnets = LeadMagnet::allByTenant($tenantId);
$products = Product::allByTenant($tenantId);

view('admin/ab-tests/form', [
    'tenant' => currentTenant(),
    'test' => $test,
    'variants' => $variants,
    'leadMagnets' => $leadMagnets,
    'products' => $products,
]);
